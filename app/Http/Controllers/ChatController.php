<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Pest\Plugins\Tia\WatchDefaults\Inertia;

class ChatController extends Controller
{
    public function index():Response
    {
        return Inertia::render('Chat');
    }
}
