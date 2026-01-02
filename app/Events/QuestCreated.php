<?php

namespace App\Events;

use App\Models\Quest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PublicChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $quest;

    /**
     * Create a new event instance.
     */
    public function __construct(Quest $quest)
    {
        $this->quest = $quest->load(['patron', 'categories', 'tags']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast to a public channel so all users can see new quests
        return [
            new PublicChannel('quests'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'quest.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->quest->id,
            'title' => $this->quest->title,
            'description' => $this->quest->description,
            'price' => $this->quest->price,
            'status' => $this->quest->status,
            'patron' => [
                'id' => $this->quest->patron->id,
                'name' => $this->quest->patron->name,
            ],
            'categories' => $this->quest->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            })->toArray(),
            'tags' => $this->quest->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            })->toArray(),
            'created_at' => $this->quest->created_at->toIso8601String(),
            'created_at_human' => $this->quest->created_at->diffForHumans(),
        ];
    }
}

