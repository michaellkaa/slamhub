<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;

class ProcessFinishedAwardEvents extends Command
{
    protected $signature = 'events:process-awards';

    protected $description = 'Po skončení award eventu přidá ocenění performerům';

    public function handle()
    {
        $events = Event::where('is_award_event', true)
            ->where('starts_at', '<=', now())
            ->where('awards_processed', false)
            ->get();

        foreach ($events as $event) {

            foreach ($event->performers as $user) {

                $user->awards()->syncWithoutDetaching([
                    $event->winner_award_id => [
                        'event_id' => $event->id
                    ]
                ]);
            }

            $event->awards_processed = true;
            $event->save();
        }

        $this->info('Awards processed.');
    }
}
