<?php

namespace App\Notifications;

use App\Models\Quest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuestApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Quest $quest,
        public bool $leveledUp = false,
        public ?int $newLevel = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Quest Approved: ' . $this->quest->title)
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('Your quest "' . $this->quest->title . '" has been approved and completed!')
            ->line('You have received ' . number_format($this->quest->price) . ' gold coins.');

        if ($this->leveledUp && $this->newLevel) {
            $message->line('🎉 You leveled up to level ' . $this->newLevel . '!');
        }

        $message->action('View Quest', url('/quests/' . $this->quest->id))
            ->line('Thank you for completing this quest!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'quest_id' => $this->quest->id,
            'quest_title' => $this->quest->title,
            'gold_earned' => $this->quest->price,
            'leveled_up' => $this->leveledUp,
            'new_level' => $this->newLevel,
        ];
    }
}

