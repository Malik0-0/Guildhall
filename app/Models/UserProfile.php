<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'avatar',
        'location',
        'website',
        'skills',
        'completed_quests',
        'cancelled_quests',
        'success_rate',
        'total_earned',
        'total_spent',
        'last_active_at',
        'average_rating',
        'total_reviews',
    ];

    protected $casts = [
        'skills' => 'array',
        'success_rate' => 'decimal:2',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reviews for this user.
     */
    public function reviews()
    {
        return $this->hasMany(UserReview::class, 'reviewed_user_id');
    }

    /**
     * Get the user skills.
     */
    public function userSkills()
    {
        return $this->hasMany(UserSkill::class);
    }

    /**
     * Calculate and update success rate.
     */
    public function updateSuccessRate()
    {
        $total = $this->completed_quests + $this->cancelled_quests;
        if ($total > 0) {
            $this->success_rate = ($this->completed_quests / $total) * 100;
        } else {
            $this->success_rate = 0;
        }
        $this->save();
    }

    
    
    /**
     * Update last active timestamp.
     */
    public function updateLastActive()
    {
        $this->update(['last_active_at' => now()]);
    }
}
