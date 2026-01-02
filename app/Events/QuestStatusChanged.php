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

class QuestStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $quest;
    public $previousStatus;
    public $action; // 'accepted', 'completed', 'approved', 'rejected', 'cancelled'

    /**
     * Create a new event instance.
     */
    public function __construct(Quest $quest, string $action, ?string $previousStatus = null)
    {
        $this->quest = $quest->load(['patron', 'adventurer', 'categories', 'tags']);
        $this->action = $action;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PublicChannel('quests'),
            new PrivateChannel('quest.' . $this->quest->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'quest.status.changed';
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
            'status' => $this->quest->status,
            'action' => $this->action,
            'previous_status' => $this->previousStatus,
            'patron' => [
                'id' => $this->quest->patron->id,
                'name' => $this->quest->patron->name,
            ],
            'adventurer' => $this->quest->adventurer ? [
                'id' => $this->quest->adventurer->id,
                'name' => $this->quest->adventurer->name,
            ] : null,
        ];
    }
}

