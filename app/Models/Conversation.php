<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}