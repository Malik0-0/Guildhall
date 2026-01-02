<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReview extends Model
{
    protected $fillable = [
        'reviewer_id',
        'reviewed_user_id',
        'quest_id',
        'rating',
        'comment',
    ];

    /**
     * Get the reviewer who wrote the review.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the user being reviewed.
     */
    public function reviewedUser()
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }

    /**
     * Get the quest associated with the review.
     */
    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Scope to get reviews for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('reviewed_user_id', $userId);
    }

    /**
     * Get formatted rating with stars.
     */
    public function getStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}
