<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

// Sent to the person who SENT a friend request, the moment it is accepted.
// ShouldBroadcastNow: published immediately (no queue worker on Render).
class FriendRequestAccepted implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $userId,          // who should be told (the original sender)
        public int $conversationId,  // the new private conversation
        public int $friendId,        // who accepted
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'friend.accepted';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'friend_id' => $this->friendId,
        ];
    }
}