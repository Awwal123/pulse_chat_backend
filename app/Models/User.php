<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'phone',
    'name',
    'profile_picture',
    'security_pin',
    'email',
    'gender',
    'birthday',
])]
#[Hidden([
    'security_pin',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'security_pin' => 'hashed',
        ];
    }
    public function sentFriendRequests(): HasMany
{
    return $this->hasMany(FriendRequest::class, 'sender_id');
}

public function receivedFriendRequests(): HasMany
{
    return $this->hasMany(FriendRequest::class, 'receiver_id');
}

public function friendships(): HasMany
{
    return $this->hasMany(Friendship::class, 'user_id');
}

public function conversationMembers(): HasMany
{
    return $this->hasMany(ConversationMember::class);
}

}