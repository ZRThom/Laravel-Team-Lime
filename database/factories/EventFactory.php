<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'sport_id' => Sport::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'starts_at' => fake()->dateTimeBetween('+1 day', '+3 months'),
            'duration_minutes' => fake()->randomElement([60, 90, 120]),
            'city' => fake()->city(),
            'venue' => fake()->streetName(),
            'capacity' => fake()->numberBetween(2, 100),
            'public_token' => Str::random(32),
            'status' => 'published',
        ];
    }

    /**
     * Indicate that the event is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the event is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
