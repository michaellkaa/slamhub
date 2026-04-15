<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaderboardMovementNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected int $previousRank,
        protected int $newRank
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $change = $this->previousRank - $this->newRank;

        return [
            'type' => 'leaderboard_movement',
            'previous_rank' => $this->previousRank,
            'new_rank' => $this->newRank,
            'change' => $change,
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'same'),
        ];
    }
}
