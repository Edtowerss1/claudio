<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Assistant;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Laravel\Ai\Vercel\Vercel;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('chat');
    }

    public function stream(Request $request)
    {
        // Handle streaming logic here
        $chat = Vercel::chat($request);

        return (new Assistant)
            ->stream($chat)
            ->usingVercelDataProtocol($chat->messageId());
    }
}
