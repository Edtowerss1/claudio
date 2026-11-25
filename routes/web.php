<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    ROute::post('chat/stream', [ChatController::class, 'stream'])->name('chat.stream');
});

require __DIR__.'/settings.php';
