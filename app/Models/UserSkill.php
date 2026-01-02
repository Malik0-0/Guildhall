<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSkill extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'level',
    ];

    /**
     * Get the user that owns the skill.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted level with dots.
     */
    public function getLevelDotsAttribute()
    {
        return str_repeat('●', $this->level) . str_repeat('○', 5 - $this->level);
    }

    /**
     * Scope to get skills by level.
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', '>=', $level);
    }
}
