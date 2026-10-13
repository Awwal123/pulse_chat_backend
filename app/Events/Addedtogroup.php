<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

// Sent to people who were just added to a group, so the group appears in their
// chat list immediately. ONE publish reaches every added person's personal channel.
// ShouldBroadcastNow: published right away (no queue worker on Render).
class AddedToGroup implements ShouldBroadcastNow
{
    use Dispatchable;

    /**
     * @param  int[]  $userIds         who was added (max 100 per event)
     * @param  int    $conversationId  the group
     */
    public function __construct(
        public array $userIds,
        public int $conversationId,
    ) {}

    public function broadcastOn(): array
    {
        return array_map(
            fn ($id) => new PrivateChannel('user.' . (int) $id),
            array_values($this->userIds)
        );
    }

    public function broadcastAs(): string
    {
        return 'group.added';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
        ];
    }
}