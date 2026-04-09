<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventVoteRound;
use App\Models\EventVoteSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventVotingTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_host_roles_can_manage_voting(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['user_id' => $organizer->id]);
        $normalUser = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($normalUser);

        $response = $this->postJson("/api/events/{$event->id}/voting/session");
        $response->assertStatus(403);
    }

    public function test_vote_is_unique_per_round_and_participant_token(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['user_id' => $organizer->id]);

        $session = EventVoteSession::create([
            'event_id' => $event->id,
            'code' => 'ABC123',
            'enabled' => true,
            'status' => 'live',
            'created_by' => $organizer->id,
        ]);

        $round = EventVoteRound::create([
            'event_vote_session_id' => $session->id,
            'performer_name' => 'Poet One',
            'state' => 'live',
            'starts_at' => now(),
        ]);

        $session->update(['current_round_id' => $round->id]);

        $join = $this->postJson('/api/voting/join', [
            'code' => 'ABC123',
            'is_anonymous' => true,
        ]);

        $join->assertOk();

        $token = $join->json('participant_token');
        $this->assertNotEmpty($token);

        $firstVote = $this->withCookie('vote_participant_token', $token)
            ->postJson("/api/events/{$event->id}/voting/cast", ['vote_value' => 1]);
        $firstVote->assertOk()->assertJson(['already_existed' => false]);

        $secondVote = $this->withCookie('vote_participant_token', $token)
            ->postJson("/api/events/{$event->id}/voting/cast", ['vote_value' => 1]);
        $secondVote->assertOk()->assertJson(['already_existed' => true]);
    }
}
