<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected User $follower
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'push'];
    }

    public function toPush(object $notifiable): array
    {
        $name = $this->follower->name ?: $this->follower->username ?: 'Někdo';

        return [
            'title' => 'Nový follower',
            'body' => $name . ' tě začal sledovat',
            'url' => '/profile/' . $this->follower->username,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_follower',
            'follower_id' => $this->follower->id,
            'follower_name' => $this->follower->name,
            'follower_username' => $this->follower->username,
            'follower_profile_pic' => $this->follower->profile_pic,
        ];
    }
}
