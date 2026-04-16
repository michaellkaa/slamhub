<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventLeagueController extends Controller
{
    private function canManage(Request $request, Event $event): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        return $user->role === 'organizer' || $user->role === 'admin';
    }

    private function defaultLeagueData(Event $event): array
    {
        return [
            'slots' => [
                ['id' => 'A', 'performer_id' => null, 'performer_name' => null],
                ['id' => 'B', 'performer_id' => null, 'performer_name' => null],
                ['id' => 'C', 'performer_id' => null, 'performer_name' => null],
            ],
            'round_robin' => ['ab' => null, 'bc' => null, 'ca' => null],
            'second_round_winner' => null,
            'final_winner' => null,
            'updated_at' => now()->toISOString(),
        ];
    }

    public function show(Request $request, Event $event)
    {
        abort_if($event->event_mode !== 'league', 422, 'Event is not a league event.');

        $event->loadMissing('performers');

        return response()->json([
            'event_id' => $event->id,
            'league_data' => $event->league_data ?: $this->defaultLeagueData($event),
            'event_performers' => $event->performers->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'username' => $p->username,
            ])->values(),
            'guest_performers' => array_values((array) $event->guest_performers),
        ]);
    }

    public function update(Request $request, Event $event)
    {
        abort_if($event->event_mode !== 'league', 422, 'Event is not a league event.');
        abort_unless($this->canManage($request, $event), 403, 'Forbidden');

        $data = $request->validate([
            'league_data' => ['required', 'array'],
        ]);

        $leagueData = $data['league_data'];
        $leagueData['updated_at'] = now()->toISOString();
        $event->league_data = $leagueData;
        $event->save();

        return response()->json(['league_data' => $event->league_data]);
    }
}
