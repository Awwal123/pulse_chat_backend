<?php

namespace App\Http\Controllers;

use App\Events\FriendRequestAccepted;
use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Http\Requests\SearchUserRequest;
use App\Http\Requests\SendFriendRequest;
use App\Http\Requests\RespondFriendRequest;
use App\Models\FriendRequest;
use App\Models\Friendship;
use App\Models\User;
use App\Traits\HttpResponses;
use App\Services\FirebaseNotificationService;

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

    public function sendRequest(
        SendFriendRequest $request,
        FirebaseNotificationService $firebaseNotificationService
    ) {
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

        $receiver = User::with('deviceTokens')->find($request->receiver_id);

        foreach ($receiver->deviceTokens as $deviceToken) {
            try {
                $firebaseNotificationService->sendToToken(
                    $deviceToken->token,
                    $sender->name,
                    'Sent you a friend request.',
                    [
                        'type' => 'friend_request',
                        'friend_request_id' => (string) $friendRequest->id,
                        'sender_id' => (string) $sender->id,
                    ]
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

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

    // ── friend suggestions ───────────────────────────────

    // A few random people to suggest. The pinned account (DevAmeer) is always first.
    public function getSuggestions()
    {
        $user = request()->user();

        $limit = min(max(request()->integer('limit', 10), 1), 20);

        $excludedIds = $this->suggestionExcludedIds($user);
        $pinned = $this->pinnedSuggestion($excludedIds);

        $random = User::whereNotIn('id', $excludedIds)
            ->when($pinned, fn ($query) => $query->where('id', '!=', $pinned->id))
            ->inRandomOrder()
            ->limit($limit - ($pinned ? 1 : 0))
            ->get(['id', 'name', 'profile_picture']);

        $suggestions = collect([$pinned])
            ->filter()
            ->concat($random)
            ->values()
            ->map(fn ($suggested) => $this->formatSuggestion($suggested, $pinned));

        return $this->success(
            $suggestions,
            'Friend suggestions retrieved successfully.'
        );
    }

    // EVERY person the user could still add, paginated (stable A-Z order so pages
    // never reshuffle). The pinned account is added at the top of page 1.
    public function getAllSuggestions()
    {
        $user = request()->user();

        $perPage = min(max(request()->integer('per_page', 20), 1), 50);

        $excludedIds = $this->suggestionExcludedIds($user);
        $pinned = $this->pinnedSuggestion($excludedIds);

        $page = User::whereNotIn('id', $excludedIds)
            ->when($pinned, fn ($query) => $query->where('id', '!=', $pinned->id))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage, ['id', 'name', 'profile_picture']);

        $items = $page->getCollection()
            ->map(fn ($suggested) => $this->formatSuggestion($suggested, $pinned));

        if ($pinned && $page->currentPage() === 1) {
            $items->prepend($this->formatSuggestion($pinned, $pinned));
        }

        return $this->success(
            [
                'data' => $items->values(),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total() + ($pinned ? 1 : 0),
            ],
            'Friend suggestions retrieved successfully.'
        );
    }

    // myself, my friends, and anyone I already have a request with (either direction):
    // sending to them would be rejected with 409 anyway.
    private function suggestionExcludedIds(User $user)
    {
        return collect([$user->id])
            ->merge(Friendship::where('user_id', $user->id)->pluck('friend_id'))
            ->merge(FriendRequest::where('sender_id', $user->id)->pluck('receiver_id'))
            ->merge(FriendRequest::where('receiver_id', $user->id)->pluck('sender_id'))
            ->unique()
            ->values();
    }

    // The default suggestion shown first to everyone (config/friends.php).
    // Matches on the last 10 digits, so 0903..., +234903... and 234903... all match.
    private function pinnedSuggestion($excludedIds): ?User
    {
        $digits = preg_replace('/\D/', '', (string) config('friends.pinned_phone'));

        if (strlen($digits) < 10) {
            return null;
        }

        return User::where('phone', 'like', '%' . substr($digits, -10))
            ->whereNotIn('id', $excludedIds)
            ->first(['id', 'name', 'profile_picture']);
    }

    // No phone numbers on purpose: this list is shown to every user.
    private function formatSuggestion(User $suggested, ?User $pinned): array
    {
        return [
            'id' => $suggested->id,
            'name' => $suggested->name,
            'profile_picture' => $suggested->profile_picture,
            'is_featured' => $pinned !== null && $suggested->id === $pinned->id,
        ];
    }

    public function respondToRequest(
        RespondFriendRequest $request,
        FriendRequest $friendRequest,
        FirebaseNotificationService $firebaseNotificationService
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

        // Tell the sender's app right away (their chat list gets the new conversation)
        try {
            broadcast(new FriendRequestAccepted(
                userId: $friendRequest->sender_id,
                conversationId: $conversation->id,
                friendId: $user->id,
            ));
        } catch (\Throwable $e) {
            report($e); // a broadcast failure must never fail the accept
        }

        // Notify the person who sent the friend request

        $sender = User::with('deviceTokens')->find($friendRequest->sender_id);

        foreach ($sender->deviceTokens as $deviceToken) {
            try {
                $firebaseNotificationService->sendToToken(
                    $deviceToken->token,
                    $user->name,
                    'Accepted your friend request.',
                    [
                        'type' => 'friend_request_accepted',
                        'friend_request_id' => (string) $friendRequest->id,
                        'receiver_id' => (string) $user->id,
                    ]
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $this->success(
            $friendRequest->fresh(),
            'Friend request accepted successfully.'
        );
    }
}