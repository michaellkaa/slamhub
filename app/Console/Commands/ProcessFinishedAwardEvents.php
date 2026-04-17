<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class ProcessFinishedAwardEvents extends Command
{
    protected $signature = 'events:process-awards';
    protected $description = 'Po skončení award eventu přidá ocenění performerům';

    public function handle()
{
    try {
        $this->info('START');

        $events = Event::where('is_award_event', true)
        ->where('awards_processed', false)
        ->whereNotNull('winner_award_id')
        ->where('starts_at', '<=', now())
        ->get();

        $this->info('Events: ' . $events->count());

        foreach ($events as $event) {

            $this->info('Event ID: ' . $event->id);

            foreach ($event->performers as $user) {

                if (!$event->winner_award_id) {
                    $this->warn("Event {$event->id} has no winner_award_id");
                    continue;
                }
            
                DB::table('award_user')->insert([
                    'user_id' => $user->id,
                    'award_id' => $event->winner_award_id,
                    'event_id' => $event->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $event->update(['awards_processed' => true]);
        }

        $this->info('DONE');

    } catch (\Throwable $e) {
        $this->error($e->getMessage());
        throw $e;
    }
}
}