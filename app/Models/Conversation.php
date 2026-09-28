<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Conversation extends Model
{
    protected $fillable = [
        'type',
        'name',
        'profile_picture',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(ConversationMember::class);
    }

    public function messages(): HasMany
{
    return $this->hasMany(Message::class);
}
public function latestMessage(): HasOne
{
    return $this->hasOne(Message::class)->latestOfMany();
}
}