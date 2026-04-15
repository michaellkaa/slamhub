<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Event $event,
        protected User $organizer
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'event_published',
            'event_id' => $this->event->id,
            'title' => $this->event->title,
            'starts_at' => $this->event->starts_at,
            'organizer_id' => $this->organizer->id,
            'organizer_name' => $this->organizer->name,
            'organizer_username' => $this->organizer->username,
        ];
    }
}
