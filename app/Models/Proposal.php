<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Proposal extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'message',
        'estimated_completion_time',
        'quest_id',
        'adventurer_id',
        'status',
    ];

    /**
     * Get the quest that this proposal belongs to.
     */
    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Get the adventurer who made this proposal.
     */
    public function adventurer()
    {
        return $this->belongsTo(User::class, 'adventurer_id');
    }

    /**
     * Scope to get only pending proposals.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get only accepted proposals.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope to get only rejected proposals.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Check if proposal is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if proposal is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if proposal is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Accept this proposal and reject others for the same quest.
     */
    public function accept()
    {
        return DB::transaction(function () {
            // Accept this proposal
            $this->update(['status' => self::STATUS_ACCEPTED]);
            
            // Update quest status and assign adventurer
            $this->quest->update([
                'adventurer_id' => $this->adventurer_id,
                'status' => Quest::STATUS_ACCEPTED,
            ]);
            
            // Reject all other pending proposals for this quest
            $this->quest->proposals()
                ->where('id', '!=', $this->id)
                ->pending()
                ->update(['status' => self::STATUS_REJECTED]);
        });
    }

    /**
     * Get all valid statuses.
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
        ];
    }
}
