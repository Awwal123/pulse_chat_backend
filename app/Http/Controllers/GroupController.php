<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddGroupMemberRequest;
use App\Http\Requests\CreateGroupRequest;
use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    use HttpResponses;

    public function store(CreateGroupRequest $request)
    {
        $user = $request->user();

        $conversation = DB::transaction(function () use ($request, $user) {

            // Create the group conversation
            $conversation = Conversation::create([
                'type' => 'group',
                'name' => $request->name,
                'profile_picture' => $request->profile_picture,
            ]);

            // Add creator as admin
            ConversationMember::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'admin',
            ]);

            // Add selected users as members
            foreach ($request->member_ids as $memberId) {

                // Don't add the creator twice
                if ($memberId == $user->id) {
                    continue;
                }

                ConversationMember::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $memberId,
                    'role' => 'member',
                ]);
            }

            return $conversation;
        });

        $conversation->load([
            'members.user',
        ]);

        return $this->success(
            $conversation,
            'Group created successfully.',
            201
        );
    }

    public function members(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        // Make sure this is a group
        if ($conversation->type !== 'group') {
            return $this->error(
                null,
                'This conversation is not a group.',
                400
            );
        }

        // Make sure the user belongs to the group
        $isMember = $conversation->members()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return $this->error(
                null,
                'You are not a member of this group.',
                403
            );
        }

        $members = $conversation->members()
            ->with('user')
            ->get();

        return $this->success(
            $members,
            'Group members retrieved successfully.'
        );
    }

    public function addMembers(
        AddGroupMemberRequest $request,
        Conversation $conversation
    ) {
        $user = $request->user();

        // Make sure this is a group
        if ($conversation->type !== 'group') {
            return $this->error(
                null,
                'This conversation is not a group.',
                400
            );
        }

        // Find the logged-in user's membership
        $membership = $conversation->members()
            ->where('user_id', $user->id)
            ->first();

        // Make sure the user belongs to the group
        if (!$membership) {
            return $this->error(
                null,
                'You are not a member of this group.',
                403
            );
        }

        // Only admins can add members
        if ($membership->role !== 'admin') {
            return $this->error(
                null,
                'Only group admins can add members.',
                403
            );
        }

        $memberIds = $request->member_ids;

        // Get users who are already members
        $existingMemberIds = $conversation->members()
            ->whereIn('user_id', $memberIds)
            ->pluck('user_id')
            ->toArray();

        // Only add users who aren't already members
        $newMemberIds = array_diff($memberIds, $existingMemberIds);

        DB::transaction(function () use ($conversation, $newMemberIds) {

            foreach ($newMemberIds as $memberId) {
                ConversationMember::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $memberId,
                    'role' => 'member',
                ]);
            }
        });

        $members = $conversation->members()
            ->with('user')
            ->get();

        return $this->success(
            $members,
            'Group members added successfully.'
        );
    }
}