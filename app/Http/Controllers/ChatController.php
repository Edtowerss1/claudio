<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Assistant;
use App\Ai\Agents\TitleWriter;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Ai\Vercel\Vercel;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('chat', [
            'id' => (string) Str::uuid7(),
            'messages' => [],
        ]);
    }

    public function show(Request $request, string $conversation)
    {
        // only allow the user to view their own conversations (else is a 404)
        $conversation = $request->user()->conversations()->findOrFail($conversation);

        return Inertia::render('chat', [
            'id' => $conversation->id,
            'messages' => Vercel::toUiMessages(
                $conversation->messages()->orderBy('id')->get()
            ),
        ]);
    }

    public function update(Request $request, string $conversation): RedirectResponse
    {
        $request->user()->conversations()->findOrFail($conversation)->update(
            $request->validate(['title' => ['required', 'string', 'max:100']])
        );

        return back();
    }

    public function destroy(Request $request, string $conversation): RedirectResponse
    {
        $conversation = $request->user()->conversations()->findOrFail($conversation);
        $conversation->messages()->delete();
        $conversation->delete();

        return to_route('chat.index');
    }

    public function stream(Request $request)
    {
        $request->validate(['id' => ['required', 'uuid']]);

        // Handle streaming logic here
        $chat = Vercel::chat($request);
        $user = $request->user();

        $conversation = Conversation::find($request->id);

        // Ensure the conversation belongs to the authenticated user
        abort_if($conversation && !$conversation->participant->is($user), 403);

        // try again: forget all messages except the first one, so we can regenerate the answer
        if ($conversation && $request->input('trigger') === 'regenerate-message') {
            $conversation->keepFirst(count($request->input('messages', [])) - 1);
        }

        $isNewConversation = $conversation === null;

        $conversation ??= $user->conversations()->create([
            'id' => $request->id,
            'title' => Str::limit($chat->message()?->content ?? 'New Conversation', 40),
        ]);

        return (new Assistant(isSmartest: $request->boolean('smartest', false)))
            ->continue($conversation->id, as: $user)
            ->stream($chat)
            ->then(function () use ($isNewConversation, $conversation, $chat) {
                // First answer done: name the chat. If that fails, keep the old title.
                if ($isNewConversation) {
                    rescue(fn() => $conversation->update([
                        'title' => Str::limit((new TitleWriter)->prompt($chat->message()->content)->text, 60),
                    ]));
                }
            })
            ->usingVercelDataProtocol($chat->messageId());
    }
}
