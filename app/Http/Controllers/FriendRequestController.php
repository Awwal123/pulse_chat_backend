<?php

namespace App\Http\Controllers;


use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Http\Requests\SearchUserRequest;
use App\Http\Requests\SendFriendRequest;
use App\Http\Requests\RespondFriendRequest;
use App\Models\FriendRequest;
use App\Models\Friendship;
use App\Models\User;
use App\Traits\HttpResponses;

class FriendRequestController extends Controller
{
    use HttpResponses;

    public function searchUser(SearchUserRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return $this->error(
                null,
                'No user found with this phone number.',
                404
            );
        }

        return $this->success(
            [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture,
            ],
            'User found.'
        );
    }

    public function sendRequest(SendFriendRequest $request)
{
    $sender = $request->user();

    if ($sender->id === $request->receiver_id) {
        return $this->error(
            null,
            'You cannot send a friend request to yourself.',
            400
        );
    }

    $existingRequest = FriendRequest::where(function ($query) use ($sender, $request) {
        $query->where('sender_id', $sender->id)
            ->where('receiver_id', $request->receiver_id);
    })->orWhere(function ($query) use ($sender, $request) {
        $query->where('sender_id', $request->receiver_id)
            ->where('receiver_id', $sender->id);
    })->first();

    if ($existingRequest) {
        return $this->error(
            null,
            'A friend request already exists between these users.',
            409
        );
    }

    $friendRequest = FriendRequest::create([
        'sender_id' => $sender->id,
        'receiver_id' => $request->receiver_id,
        'status' => 'pending',
    ]);

    return $this->success(
        $friendRequest,
        'Friend request sent successfully.',
        201
    );
}

public function getFriendRequests()
{
    $user = request()->user();

    $requests = FriendRequest::with([
        'sender:id,name,phone,profile_picture'
    ])
        ->where('receiver_id', $user->id)
        ->where('status', 'pending')
        ->latest()
        ->get()
        ->map(function ($request) {
            return [
                'friend_request_id' => $request->id,
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'status' => $request->status,
                'responded_at' => $request->responded_at,
                'created_at' => $request->created_at,
                'sender' => $request->sender,
            ];
        });

    return $this->success(
        $requests,
        'Friend requests retrieved successfully.'
    );
}

public function getFriends()
{
    $user = request()->user();

    $friendships = Friendship::with([
        'friend:id,name,phone,profile_picture'
    ])
        ->where('user_id', $user->id)
        ->latest()
        ->get()
        ->map(function ($friendship) {
            return [
                'friendship_id' => $friendship->id,
                'friend' => $friendship->friend,
            ];
        });

    return $this->success(
        $friendships,
        'Friends retrieved successfully.'
    );
}

public function respondToRequest(
    RespondFriendRequest $request,
    FriendRequest $friendRequest
) {
    $user = $request->user();

    if ($friendRequest->receiver_id !== $user->id) {
        return $this->error(
            null,
            'You are not allowed to respond to this friend request.',
            403
        );
    }

    if ($friendRequest->status !== 'pending') {
        return $this->error(
            null,
            'This friend request has already been responded to.',
            409
        );
    }

    if ($request->action === 'reject') {
        $friendRequest->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        return $this->success(
            $friendRequest->fresh(),
            'Friend request rejected successfully.'
        );
    }

    // Accept the friend request

    Friendship::create([
        'user_id' => $friendRequest->sender_id,
        'friend_id' => $friendRequest->receiver_id,
    ]);

    Friendship::create([
        'user_id' => $friendRequest->receiver_id,
        'friend_id' => $friendRequest->sender_id,
    ]);

    $conversation = Conversation::create([
    'type' => 'private',
]);

ConversationMember::create([
    'conversation_id' => $conversation->id,
    'user_id' => $friendRequest->sender_id,
]);

ConversationMember::create([
    'conversation_id' => $conversation->id,
    'user_id' => $friendRequest->receiver_id,
]);
    $friendRequest->update([
        'status' => 'accepted',
        'responded_at' => now(),
    ]);

    return $this->success(
        $friendRequest->fresh(),
        'Friend request accepted successfully.'
    );
}

}