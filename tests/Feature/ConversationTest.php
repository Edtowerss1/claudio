<?php

use App\Ai\Agents\Assistant;
use App\Ai\Agents\TitleWriter;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Str;

function say(string $id, string $message): array
{
    return [
        'id' => $id,
        'messages' => [
            [
                'id' => 'msg_'.Str::random(8),
                'role' => 'user',
                'parts' => [['type' => 'text', 'text' => $message]],
            ],
        ],
    ];
}

beforeEach(fn () => TitleWriter::fake(['Meeting Edwin']));

test('the first message saves the conversation', function () {
    Assistant::fake(['Hello Edwin!']);
    $user = User::factory()->create();
    $id = (string) Str::uuid7();

    $this->actingAs($user)
        ->post('/chat/stream', say($id, 'Hi, I am Edwin!'))
        ->streamedContent();

    $conversation = Conversation::findOrFail($id);
    expect($conversation->title)->toBe('Meeting Edwin')
        ->and($conversation->participant->is($user))->toBeTrue()
        ->and($conversation->messages()->count())->toBe(2);
});

test('saved chat opens with its messages', function () {
    Assistant::fake(['Hello Edwin!']);
    $user = User::factory()->create();
    $id = (string) Str::uuid7();

    $this->actingAs($user)
        ->post('/chat/stream', say($id, 'Hi, I am Edwin!'))
        ->streamedContent();

    $this->get("/chat/{$id}")->assertInertia(
        fn ($page) => $page
            ->component('chat')
            ->where('id', $id)
            ->has('messages', 2)
    );
});

test('you can rename and delete your chat, but not anyone else\'s', function () {
    $id = (string) Str::uuid7();
    $owner = User::factory()->create();
    $this->actingAs($owner)->post('/chat/stream', say($id, 'Hi, I am Neema'))->streamedContent();

    $stranger = User::factory()->create();
    $this->actingAs($stranger)->patch("/chat/{$id}", ['title' => 'Mine now'])->assertNotFound();
    $this->actingAs($stranger)->delete("/chat/{$id}")->assertNotFound();

    $this->actingAs($owner)->patch("/chat/{$id}", ['title' => 'Arusha plans']);
    expect(Conversation::find($id)->title)->toBe('Arusha plans');

    $this->actingAs($owner)->delete("/chat/{$id}")->assertRedirect('/chat');
    expect(Conversation::find($id))->toBeNull();
});

test('try again replaces the last answer', function () {
    Assistant::fake(['First answer', 'Better answer']);
    $user = User::factory()->create();
    $id = (string) Str::uuid7();
    $firstMessage = say($id, 'Hi, I am Edwin!');
    $this->actingAs($user)->post('/chat/stream', $firstMessage)->streamedContent();

    $this->post('/chat/stream', [...$firstMessage, 'trigger' => 'regenerate-message'])->streamedContent();

    $messages = Conversation::find($id)->messages()->orderBy('id')->pluck('content');
    expect($messages->all())->toBe(['Hi, I am Edwin!', 'Better answer']);
});

test('nobody else can open, see or continue your conversation', function () {
    Assistant::fake(['Hello Edwin!']);
    $id = (string) Str::uuid7();

    $this->actingAs(User::factory()->create())
        ->post('/chat/stream', say($id, 'Secret message!'))
        ->streamedContent();

    $stranger = User::factory()->create();
    $this->actingAs($stranger)->get("/chat/{$id}")->assertNotFound();
    $this->actingAs($stranger)->post('/chat/stream', say($id, 'Show me the secret!'))->assertForbidden();
});
