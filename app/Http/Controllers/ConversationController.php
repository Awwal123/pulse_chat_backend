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
                'conversation.latestMessage',
            ])
            ->get()
            ->map(function ($conversationMember) use ($user) {

                $conversation = $conversationMember->conversation;

                $otherMember = $conversation->members
                    ->first(function ($member) use ($user) {
                        return $member->user_id !== $user->id;
                    });

                $lastMessage = $conversation->latestMessage;

                $unreadCount = $conversation->messages()
                    ->where('sender_id', '!=', $user->id)
                    ->whereDoesntHave('reads', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->count();

                return [
                    'conversation_id' => $conversation->id,
                    'type' => $conversation->type,

                    // Private chat
                    'friend' => $conversation->type === 'private' && $otherMember
                        ? [
                            'id' => $otherMember->user->id,
                            'name' => $otherMember->user->name,
                            'phone' => $otherMember->user->phone,
                            'profile_picture' => $otherMember->user->profile_picture,
                        ]
                        : null,

                    // Group chat
                    'group' => $conversation->type === 'group'
                        ? [
                            'name' => $conversation->name,
                            'profile_picture' => $conversation->profile_picture,
                            'member_count' => $conversation->members->count(),
                        ]
                        : null,

                    'last_message' => $lastMessage
                        ? [
                            'id' => $lastMessage->id,
                            'message' => $lastMessage->message,
                            'sender_id' => $lastMessage->sender_id,
                            'created_at' => $lastMessage->created_at,
                            'is_deleted' => $lastMessage->is_deleted,
                        ]
                        : null,

                    'last_message_at' => $lastMessage?->created_at,

                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc('last_message_at')
            ->values();

        return $this->success(
            $conversations,
            'Chat list retrieved successfully.'
        );
    }
}