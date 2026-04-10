<?php

namespace App\Http\Controllers;

use App\Models\EventVote;
use App\Models\EventVoteParticipant;
use App\Models\EventVoteSession;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class EventVotingController extends Controller
{
    private function resolveParticipantToken(Request $request): ?string
    {
        return $request->cookie('vote_participant_token')
            ?? $request->header('X-Vote-Participant-Token')
            ?? $request->input('participant_token');
    }

    public function sessionStatus($eventId)
    {
        $session = EventVoteSession::with('currentRound')
            ->where('event_id', $eventId)
            ->first();

        if (!$session) {
            return response()->json([
                'enabled' => false,
                'status' => 'draft',
                'current_round' => null,
            ]);
        }

        return response()->json([
            'enabled' => (bool) $session->enabled,
            'status' => $session->status,
            'current_round' => $session->currentRound,
        ]);
    }

    public function joinByCode(Request $request)
    {
        $payload = $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'is_anonymous' => ['nullable', 'boolean'],
            'nickname' => ['nullable', 'string', 'max:60'],
            'participant_token' => ['nullable', 'string', 'max:255'],
        ]);

        $normalizedCode = strtoupper(trim($payload['code']));
        $session = EventVoteSession::where('code', $normalizedCode)->firstOrFail();

        abort_if(!$session->enabled || $session->status === 'closed', 422, 'Voting is not active.');

        $token = $this->resolveParticipantToken($request) ?? Str::random(40);
        $isAnonymous = (bool) ($payload['is_anonymous'] ?? true);
        $user = $request->user();

        if (!$user) {
            $isAnonymous = true;
        }

        $participant = EventVoteParticipant::updateOrCreate(
            [
                'event_vote_session_id' => $session->id,
                'participant_token' => $token,
            ],
            [
                'user_id' => $isAnonymous ? null : $user?->id,
                'nickname' => $payload['nickname'] ?? null,
                'is_anonymous' => $isAnonymous,
            ]
        );

        return response()
            ->json([
                'participant_id' => $participant->id,
                'participant_token' => $token,
                'session_id' => $session->id,
                'event_id' => $session->event_id,
            ])
            ->cookie('vote_participant_token', $token, 60 * 24 * 7, '/', null, false, true);
    }

    public function liveRound(Request $request, $eventId)
    {
        try {
            $session = EventVoteSession::with('currentRound')->where('event_id', $eventId)->first();
        } catch (QueryException $e) {
            return response()->json(['message' => 'Database unavailable'], 503);
        }

        if (!$session) {
            return response()->json([
                'enabled' => false,
                'status' => 'draft',
                'session_id' => null,
                'current_round' => null,
                'already_voted' => false,
            ]);
        }

        $response = [
            'enabled' => (bool) $session->enabled,
            'status' => $session->status,
            'session_id' => $session->id,
            'current_round' => $session->currentRound,
        ];

        if ($session->currentRound) {
            $token = $this->resolveParticipantToken($request);
            $participant = $token
                ? EventVoteParticipant::where('event_vote_session_id', $session->id)
                    ->where('participant_token', $token)
                    ->first()
                : null;

            $alreadyVoted = false;
            if ($participant) {
                $alreadyVoted = EventVote::where('event_vote_round_id', $session->currentRound->id)
                    ->where('event_vote_participant_id', $participant->id)
                    ->exists();
            }

            $response['already_voted'] = $alreadyVoted;
        }

        return response()->json($response);
    }

    public function castVote(Request $request, $eventId)
    {
        $payload = $request->validate([
            'vote_value' => ['required', 'integer', 'between:1,10'],
            'participant_token' => ['nullable', 'string', 'max:255'],
        ]);

        $session = EventVoteSession::with('currentRound')->where('event_id', $eventId)->firstOrFail();
        abort_if(!$session->enabled || !$session->currentRound, 422, 'No active round.');
        abort_if($session->currentRound->state !== 'live', 422, 'Round is not live.');

        $token = $this->resolveParticipantToken($request);
        abort_if(!$token, 422, 'Join session first.');

        $participant = EventVoteParticipant::where('event_vote_session_id', $session->id)
            ->where('participant_token', $token)
            ->firstOrFail();

        $vote = EventVote::firstOrCreate(
            [
                'event_vote_round_id' => $session->currentRound->id,
                'event_vote_participant_id' => $participant->id,
            ],
            [
                'vote_value' => $payload['vote_value'],
                'cast_at' => now(),
            ]
        );

        return response()->json([
            'vote_id' => $vote->id,
            'round_id' => $session->currentRound->id,
            'already_existed' => !$vote->wasRecentlyCreated,
            'cast_at' => $vote->cast_at,
        ]);
    }
}
