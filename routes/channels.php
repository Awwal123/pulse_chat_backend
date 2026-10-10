<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversation}', function ($user, Conversation $conversation) {
    \Log::info('Broadcast auth attempt', [
        'user_id' => $user?->id,
        'conversation_id' => $conversation->id,
        'members' => $conversation->members()->pluck('user_id')->toArray(),
    ]);

    return $conversation->members()
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});