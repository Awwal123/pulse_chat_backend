<?php

namespace App\Http\Controllers;

use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    use HttpResponses;

    public function getChatList(Request $request)
    {
        $user = $request->user();

        $conversations = $user->conversationMembers()
            ->with([
                'conversation.members.user',
                'conversation.messages' => function ($query) {
                    $query->latest()->limit(1);
                },
            ])
            ->get()
            ->map(function ($conversationMember) use ($user) {

                $conversation = $conversationMember->conversation;

                $otherMember = $conversation->members
                    ->first(function ($member) use ($user) {
                        return $member->user_id !== $user->id;
                    });

                $lastMessage = $conversation->messages->first();

                return [
                    'conversation_id' => $conversation->id,
                    'type' => $conversation->type,

                    'friend' => $otherMember
                        ? [
                            'id' => $otherMember->user->id,
                            'name' => $otherMember->user->name,
                            'phone' => $otherMember->user->phone,
                            'profile_picture' => $otherMember->user->profile_picture,
                        ]
                        : null,

                    'last_message' => $lastMessage
                        ? [
                            'id' => $lastMessage->id,
                            'message' => $lastMessage->message,
                            'sender_id' => $lastMessage->sender_id,
                            'created_at' => $lastMessage->created_at,
                        ]
                        : null,
                ];
            });

        return $this->success(
            $conversations,
            'Chat list retrieved successfully.'
        );
    }
}