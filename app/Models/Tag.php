<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Get the quests for this tag.
     */
    public function quests()
    {
        return $this->belongsToMany(Quest::class, 'quest_tag');
    }

    /**
     * Get quest count for this tag.
     */
    public function getQuestCountAttribute()
    {
        return $this->quests()->where('status', 'open')->count();
    }
}
