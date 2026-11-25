<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Assistant;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Laravel\Ai\Vercel\Vercel;
use Illuminate\Support\Str;
use App\Models\Conversation;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('chat', [
            'id' => (string) Str::uuid7(),
            'messages' => [],
        ]);
    }

    public function show(Request $request, Conversation $conversation)
    {
        // only allow the user to view their own conversations (else is a 404)
        $conversation = $request->user()->conversations()->findOrFail($conversation->id);

        return Inertia::render('chat', [
            'id' => $conversation->id,
            'messages' => Vercel::toUiMessages(
                $conversation->messages()->orderBy('id')->get()
            ),
        ]);
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

        $conversation ??= $user->conversations()->create([
            'id' => $request->id,
            'title' => Str::limit($chat->message()?->content ?? 'New Conversation', 40),
        ]);

        return (new Assistant)
            ->continue($conversation->id, as: $user)
            ->stream($chat)
            ->usingVercelDataProtocol($chat->messageId());
    }
}
