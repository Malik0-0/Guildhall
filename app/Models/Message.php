<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'quest_id',
        'sender_id',
        'receiver_id',
        'message',
        'is_read',
        'read_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Get the quest this message belongs to.
     */
    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Get the user who sent this message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who receives this message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Scope to get unread messages for a user.
     */
    public function scopeUnreadFor($query, $userId)
    {
        return $query->where('receiver_id', $userId)
                     ->where('is_read', false);
    }

    /**
     * Scope to get messages for a quest.
     */
    public function scopeForQuest($query, $questId)
    {
        return $query->where('quest_id', $questId)
                     ->orderBy('created_at', 'asc');
    }
}

