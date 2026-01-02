<?php

namespace App\Notifications;

use App\Models\Quest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuestEvidenceSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject('Evidence Submitted: ' . $this->quest->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('An adventurer has submitted evidence for your quest "' . $this->quest->title . '".')
            ->line('Please review the evidence and approve or reject it.')
            ->line('If you do not take action within 72 hours, the quest will be automatically approved.')
            ->action('Review Evidence', url('/quests/' . $this->quest->id))
            ->line('Thank you for using Guildhall!');
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
        ];
    }
}

