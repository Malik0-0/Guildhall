<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    // Quest status constants
    const STATUS_OPEN = 'open';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'title',
        'description',
        'evidence',
        'evidence_files',
        'rejection_reason',
        'price',
        'status',
        'patron_id',
        'adventurer_id',
        'submitted_at',
        'approved_at',
        'rejected_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'evidence_files' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * Get the patron (user who created the quest).
     */
    public function patron()
    {
        return $this->belongsTo(User::class, 'patron_id');
    }

    /**
     * Get the adventurer (user who accepted the quest).
     */
    public function adventurer()
    {
        return $this->belongsTo(User::class, 'adventurer_id');
    }

    /**
     * Get the categories for this quest.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'quest_category');
    }

    /**
     * Get the tags for this quest.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'quest_tag');
    }

    /**
     * Get the reviews for this quest.
     */
    public function reviews()
    {
        return $this->hasMany(UserReview::class, 'quest_id');
    }

    /**
     * Get the messages for this quest.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the proposals for this quest.
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    /**
     * Scope to get only open quests.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope to get quests by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    /**
     * Scope to get quests by tag.
     */
    public function scopeByTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tags.id', $tagId);
        });
    }

    /**
     * Scope to search quests by title or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by price range.
     */
    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }
        return $query;
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate = null, $endDate = null)
    {
        if ($startDate !== null) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate !== null) {
            $query->where('created_at', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Check if quest is open.
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if quest is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if quest is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if quest is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if quest can be accepted.
     */
    public function canBeAccepted(): bool
    {
        return $this->isOpen() && $this->adventurer_id === null;
    }

    /**
     * Check if quest can be completed (evidence submission).
     */
    public function canBeCompleted(): bool
    {
        return $this->isAccepted() && $this->adventurer_id !== null;
    }

    /**
     * Check if quest can be approved by patron.
     */
    public function canBeApproved(): bool
    {
        return $this->isPendingApproval() && $this->patron_id !== null;
    }

    /**
     * Check if quest can be cancelled.
     * Only OPEN quests can be cancelled (before acceptance).
     * Once accepted by an adventurer, quest cannot be cancelled by patron.
     */
    public function canBeCancelled(): bool
    {
        return $this->isOpen() && !$this->isCompleted() && !$this->isCancelled();
    }

    /**
     * Get all valid statuses.
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_ACCEPTED,
            self::STATUS_PENDING_APPROVAL,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Check if quest is pending approval.
     */
    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }
}
