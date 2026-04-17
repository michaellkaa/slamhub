<?php

namespace App\Listeners;

use App\Events\EventCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\EventPublishedNotification;
use App\Models\User;
class SendEventNotifications
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventCreated $event): void
    {
        $eventModel = $event->event;
        $organizer = $event->organizer;

        // 🎯 vyber cílové uživatele (uprav si podle projektu)
        $users = User::where('id', '!=', $organizer->id)->get();

        foreach ($users as $user) {
            $user->notify(
                new EventPublishedNotification($eventModel, $organizer)
            );
        }
    }
}
