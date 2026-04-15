<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'location' => fake()->city(),
            'ticket_url' => fake()->url(),
            'cover_image' => null,
            'event_mode' => 'regular',
            'is_award_event' => false,
            'winner_award_id' => null,
            'league_data' => null,
        ];
    }
}
