<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    const ROLE_QUEST_GIVER = 'quest_giver';
    const ROLE_ADVENTURER = 'adventurer';
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $user->getOrCreateProfile();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'gold',
        'xp',
        'level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all quests created by this user (as patron).
     */
    public function createdQuests()
    {
        return $this->hasMany(Quest::class, 'patron_id');
    }

    /**
     * Get all quests accepted by this user (as adventurer).
     */
    public function acceptedQuests()
    {
        return $this->hasMany(Quest::class, 'adventurer_id');
    }

    /**
     * Add gold to user's balance.
     */
    public function addGold($amount)
    {
        $this->increment('gold', $amount);
        return $this;
    }

    /**
     * Deduct gold from user's balance.
     * 
     * @param int $amount The amount of gold to deduct
     * @return bool True on success
     * @throws \App\Exceptions\InsufficientGoldException if user doesn't have enough gold
     */
    public function deductGold(int $amount): bool
    {
        if ($this->gold < $amount) {
            throw new \App\Exceptions\InsufficientGoldException($this->gold, $amount);
        }
        
        $this->decrement('gold', $amount);
        return true;
    }

    /**
     * Check if user has enough gold.
     */
    public function hasEnoughGold($amount)
    {
        return $this->gold >= $amount;
    }

    /**
     * Get user skills.
     */
    public function userSkills()
    {
        return $this->hasMany(UserSkill::class);
    }

    /**
     * Get the user profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get reviews written by this user.
     */
    public function writtenReviews()
    {
        return $this->hasMany(UserReview::class, 'reviewer_id');
    }

    /**
     * Get reviews received by this user.
     */
    public function receivedReviews()
    {
        return $this->hasMany(UserReview::class, 'reviewed_user_id');
    }

    /**
     * Get messages sent by this user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get unread messages count.
     */
    public function getUnreadMessagesCountAttribute(): int
    {
        return $this->receivedMessages()->where('is_read', false)->count();
    }

    /**
     * Get all reviews for this user (both written and received).
     */
    public function reviews()
    {
        return $this->hasMany(UserReview::class, 'reviewed_user_id');
    }

    /**
     * Get or create user profile.
     */
    public function getOrCreateProfile()
    {
        return $this->profile ?? $this->profile()->create([
            'completed_quests' => 0,
            'cancelled_quests' => 0,
            'total_earned' => 0,
            'total_spent' => 0,
        ]);
    }

    /**
     * Update user stats after quest completion.
     */
    public function updateQuestStats($completed = true, $amount = 0)
    {
        $profile = $this->getOrCreateProfile();
        
        if ($completed) {
            $profile->completed_quests++;
            if ($this->role === self::ROLE_ADVENTURER) {
                $profile->total_earned += $amount;
            }
        } else {
            $profile->cancelled_quests++;
        }
        
        $profile->updateSuccessRate();
        $profile->updateLastActive();
    }

    /**
     * Add XP to user and update level.
     */
    public function addXP(int $amount): self
    {
        $this->increment('xp', $amount);
        $this->updateLevel();
        return $this;
    }

    /**
     * Calculate and update user level based on XP.
     */
    public function updateLevel(): self
    {
        $xp = $this->xp ?? 0;
        $currentLevel = $this->level ?? 1;
        $newLevel = \App\Services\QuestService::calculateLevel($xp);
        
        if ($newLevel > $currentLevel) {
            $this->level = $newLevel;
            $this->save();
        }
        
        return $this;
    }

    /**
     * Get XP required for next level.
     */
    public function getXpForNextLevelAttribute(): int
    {
        $level = $this->level ?? 1;
        return (($level + 1) * ($level + 1) - 1) * 100;
    }

    /**
     * Get XP progress to next level (0-100).
     */
    public function getXpProgressAttribute(): float
    {
        $xp = $this->xp ?? 0;
        $level = $this->level ?? 1;
        $xpForCurrentLevel = ($level - 1) * ($level - 1) * 100;
        $xpForNextLevel = $this->xp_for_next_level;
        $xpRange = $xpForNextLevel - $xpForCurrentLevel;
        
        if ($xpRange <= 0) {
            return 100;
        }
        
        $currentProgress = $xp - $xpForCurrentLevel;
        return min(100, max(0, ($currentProgress / $xpRange) * 100));
    }

    /**
     * Get trust tier based on completed quests and rating.
     */
    public function getTrustTierAttribute(): string
    {
        $profile = $this->profile;
        if (!$profile) {
            return 'bronze';
        }

        $completed = $profile->completed_quests;
        $rating = $profile->average_rating ?? 0;

        if ($completed >= 50 && $rating >= 4.5) {
            return 'diamond';
        }
        if ($completed >= 25 && $rating >= 4.0) {
            return 'platinum';
        }
        if ($completed >= 10 && $rating >= 3.5) {
            return 'gold';
        }
        if ($completed >= 5 && $rating >= 3.0) {
            return 'silver';
        }
        
        return 'bronze';
    }

    /**
     * Get trust tier display name.
     */
    public function getTrustTierNameAttribute(): string
    {
        return ucfirst($this->trust_tier);
    }

    /**
     * Get trust tier color classes.
     */
    public function getTrustTierColorAttribute(): string
    {
        return match($this->trust_tier) {
            'diamond' => 'text-blue-300 bg-blue-900/30 border-blue-500',
            'platinum' => 'text-gray-200 bg-gray-700 border-gray-500',
            'gold' => 'text-yellow-300 bg-yellow-900/30 border-yellow-500',
            'silver' => 'text-gray-300 bg-gray-600 border-gray-400',
            default => 'text-orange-300 bg-orange-900/30 border-orange-500',
        };
    }
}
