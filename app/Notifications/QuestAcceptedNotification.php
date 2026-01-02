<?php

namespace App\Notifications;

use App\Models\Quest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuestAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Quest $quest
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
        return (new MailMessage)
            ->subject('Quest Accepted: ' . $this->quest->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your quest "' . $this->quest->title . '" has been accepted by an adventurer.')
            ->line('Quest Details:')
            ->line('- Title: ' . $this->quest->title)
            ->line('- Price: ' . number_format($this->quest->price) . ' gold coins')
            ->line('- Adventurer: ' . $this->quest->adventurer->name)
            ->action('View Quest', url('/quests/' . $this->quest->id))
            ->line('You will be notified when the adventurer submits evidence for review.');
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
            'adventurer_name' => $this->quest->adventurer->name,
        ];
    }
}

