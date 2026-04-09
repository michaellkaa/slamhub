<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventVoteRound;
use App\Models\EventVoteSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventVotingHostController extends Controller
{
    public function upsertSession(Request $request, Event $event)
    {
        $user = $request->user();
        abort_unless($user && $user->canManageEventVoting($event), 403, 'Forbidden');

        $session = EventVoteSession::firstOrCreate(
            ['event_id' => $event->id],
            [
                'code' => strtoupper(Str::random(6)),
                'enabled' => false,
                'status' => 'draft',
                'created_by' => $user->id,
            ]
        );

        return response()->json($session->fresh()->load(['currentRound', 'rounds' => function ($query) {
            $query->latest('id');
        }]));
    }

    public function toggleEnabled(Request $request, Event $event)
    {
        $user = $request->user();
        abort_unless($user && $user->canManageEventVoting($event), 403, 'Forbidden');

        $payload = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $session = EventVoteSession::firstOrCreate(
            ['event_id' => $event->id],
            [
                'code' => strtoupper(Str::random(6)),
                'status' => 'draft',
                'created_by' => $user->id,
            ]
        );

        $session->enabled = $payload['enabled'];
        if (!$session->enabled) {
            $session->status = 'paused';
        } elseif ($session->status === 'draft' || $session->status === 'paused') {
            $session->status = 'live';
        }
        $session->save();

        return response()->json($session->fresh());
    }

    public function rotateCode(Request $request, Event $event)
    {
        $user = $request->user();
        abort_unless($user && $user->canManageEventVoting($event), 403, 'Forbidden');

        $session = EventVoteSession::where('event_id', $event->id)->firstOrFail();
        $session->code = strtoupper(Str::random(6));
        $session->save();

        return response()->json(['code' => $session->code]);
    }

    public function createRound(Request $request, Event $event)
    {
        $user = $request->user();
        abort_unless($user && $user->canManageEventVoting($event), 403, 'Forbidden');

        $payload = $request->validate([
            'performer_name' => ['required', 'string', 'max:255'],
            'performer_id' => ['nullable', 'exists:users,id'],
        ]);

        $session = EventVoteSession::firstOrCreate(
            ['event_id' => $event->id],
            ['code' => strtoupper(Str::random(6)), 'created_by' => $user->id]
        );

        $round = EventVoteRound::create([
            'event_vote_session_id' => $session->id,
            'performer_id' => $payload['performer_id'] ?? null,
            'performer_name' => $payload['performer_name'],
            'state' => 'pending',
        ]);

        return response()->json($round, 201);
    }

    public function startRound(Request $request, Event $event, EventVoteRound $round)
    {
        $user = $request->user();
        abort_unless($user && $user->canManageEventVoting($event), 403, 'Forbidden');

        $session = EventVoteSession::where('event_id', $event->id)->firstOrFail();
        abort_if((int) $round->event_vote_session_id !== (int) $session->id, 422, 'Round does not belong to event session');

        DB::transaction(function () use ($session, $round) {
            EventVoteRound::where('event_vote_session_id', $session->id)
                ->where('state', 'live')
                ->update(['state' => 'closed', 'ends_at' => now()]);

            $round->state = 'live';
            $round->starts_at = now();
            $round->ends_at = null;
            $round->save();

            $session->current_round_id = $round->id;
            $session->status = 'live';
            $session->enabled = true;
            $session->save();
        });

        return response()->json($round->fresh());
    }

    public function closeRound(Request $request, Event $event, EventVoteRound $round)
    {
        $user = $request->user();
        abort_unless($user && $user->canManageEventVoting($event), 403, 'Forbidden');

        $session = EventVoteSession::where('event_id', $event->id)->firstOrFail();
        abort_if((int) $round->event_vote_session_id !== (int) $session->id, 422, 'Round does not belong to event session');

        if ($round->state !== 'closed') {
            $round->state = 'closed';
            $round->ends_at = now();
            $round->save();
        }

        if ((int) $session->current_round_id === (int) $round->id) {
            $session->current_round_id = null;
            $session->save();
        }

        return response()->json($round->fresh());
    }

    public function liveResults(Request $request, Event $event)
    {
        $user = $request->user();
        abort_unless($user && $user->canManageEventVoting($event), 403, 'Forbidden');

        $session = EventVoteSession::where('event_id', $event->id)->firstOrFail();
        $round = $session->currentRound;

        if (!$round) {
            return response()->json([
                'session' => $session,
                'round' => null,
                'totals' => ['votes' => 0, 'score' => 0],
            ]);
        }

        $totals = $round->votes()
            ->selectRaw('COUNT(*) as votes, COALESCE(SUM(vote_value), 0) as score')
            ->first();

        return response()->json([
            'session' => $session,
            'round' => $round,
            'rounds' => $session->rounds()->latest('id')->get(),
            'totals' => [
                'votes' => (int) ($totals->votes ?? 0),
                'score' => (int) ($totals->score ?? 0),
            ],
        ]);
    }
}
