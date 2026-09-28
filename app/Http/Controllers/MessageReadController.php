<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarkMessageAsReadRequest;
use App\Models\Message;
use App\Models\MessageRead;
use App\Traits\HttpResponses;

class MessageReadController extends Controller
{
    use HttpResponses;

    public function markAsRead(MarkMessageAsReadRequest $request)
    {
        $user = $request->user();

        $message = Message::findOrFail($request->message_id);

        $isMember = $message->conversation
            ->members()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return $this->error(
                null,
                'You are not a member of this conversation.',
                403
            );
        }

        $messageRead = MessageRead::firstOrCreate(
            [
                'message_id' => $message->id,
                'user_id' => $user->id,
            ],
            [
                'read_at' => now(),
            ]
        );

        return $this->success(
            $messageRead,
            'Message marked as read.'
        );
    }

    public function getReadStatus(Message $message)
{
    $user = request()->user();

    $isMember = $message->conversation
        ->members()
        ->where('user_id', $user->id)
        ->exists();

    if (!$isMember) {
        return $this->error(
            null,
            'You are not a member of this conversation.',
            403
        );
    }

    $reads = $message->reads()
        ->with('user:id,name,phone,profile_picture')
        ->get();

    return $this->success(
        $reads,
        'Message read status retrieved successfully.'
    );
}
}