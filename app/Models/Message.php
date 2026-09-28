<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'reply_to_id',
        'edited_at',
        'deleted_at',
    ];

    protected $appends = [
    'is_deleted',
];
    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function reads(): HasMany
{
    return $this->hasMany(MessageRead::class);
}

public function getIsDeletedAttribute(): bool
{
    return $this->deleted_at !== null;
}
}