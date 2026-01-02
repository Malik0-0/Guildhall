<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the quests for this category.
     */
    public function quests()
    {
        return $this->belongsToMany(Quest::class, 'quest_category');
    }

    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get quest count for this category.
     */
    public function getQuestCountAttribute()
    {
        return $this->quests()->where('status', 'open')->count();
    }
}
