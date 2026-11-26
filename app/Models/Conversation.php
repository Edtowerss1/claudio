<?php

namespace App\Models;

use Laravel\Ai\Models\Conversation as AiConversation;

class Conversation extends AiConversation
{
    public function keepFirst(int $count): void
    {
        $this->messages()->orderBy('id')->get()->slice($count)->each->delete();
    }
}
