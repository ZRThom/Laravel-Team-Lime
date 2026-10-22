<?php

namespace Tests\Unit\event;

use App\Actions\Event\StoreEventAction;
use App\DTOs\Event\EventDTO;
use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreEventActionTest extends TestCase
{
    use RefreshDatabase;

    private function dto(): EventDTO
    {
        return EventDTO::from([
            'organization_id' => Organization::factory()->create()->id,
            'sport_id' => Sport::factory()->create()->id,
            'title' => 'Match du jeudi',
            'description' => null,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 90,
            'city' => 'Paris',
            'venue' => 'Stade Charlety',
            'capacity' => 10,
        ]);
    }

    public function test_it_creates_a_published_event_with_creator_and_public_token(): void
    {
        $creator = User::factory()->create();

        $event = (new StoreEventAction())->execute($this->dto(), $creator);

        $this->assertDatabaseHas('events', ['id' => $event->id, 'title' => 'Match du jeudi', 'capacity' => 10]);
        $this->assertSame('published', $event->status);
        $this->assertSame($creator->id, $event->created_by);
        $this->assertSame(32, strlen($event->public_token));
    }

    public function test_each_event_gets_a_different_token(): void
    {
        $creator = User::factory()->create();

        $first = (new StoreEventAction())->execute($this->dto(), $creator);
        $second = (new StoreEventAction())->execute($this->dto(), $creator);

        $this->assertNotSame($first->public_token, $second->public_token);
    }
}
