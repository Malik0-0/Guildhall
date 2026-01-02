<?php

namespace App\Notifications;

use App\Models\Quest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuestRejectedNotification extends Notification implements ShouldQueue
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
            ->subject('Quest Evidence Rejected: ' . $this->quest->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The evidence you submitted for "' . $this->quest->title . '" has been rejected.')
            ->line('Rejection Reason:')
            ->line($this->quest->rejection_reason)
            ->line('You can resubmit improved evidence for this quest.')
            ->action('View Quest', url('/quests/' . $this->quest->id))
            ->line('Please review the feedback and submit again.');
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
            'rejection_reason' => $this->quest->rejection_reason,
        ];
    }
}

