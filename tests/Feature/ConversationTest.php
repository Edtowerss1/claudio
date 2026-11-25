<?php

use Illuminate\Support\Str;
use App\Ai\Agents\Assistant;
use App\Models\User;
use App\Models\Conversation;


function say(string $id, string $message): array
{
    return [
        'id' => $id,
        'messages' => [
            [
                'id' => 'msg_' . Str::random(8),
                'role' => 'user',
                'parts' => [['type' => 'text', 'text' => $message]],
            ],
        ]
    ];
}


test('the first message saves the conversation', function () {
    Assistant::fake(['Hello Edwin!']);
    $user = User::factory()->create();
    $id = (string) Str::uuid7();

    $this->actingAs($user)
        ->post('/chat/stream', say($id, 'Hi, I am Edwin!'))
        ->streamedContent();

    $conversation = Conversation::findOrFail($id);
    expect($conversation->title)->toBe('Hi, I am Edwin!')
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
        fn($page) => $page
            ->component('chat')
            ->where('id', $id)
            ->has('messages', 2)
    );
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
