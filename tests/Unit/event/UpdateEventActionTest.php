<?php

namespace Tests\Unit\event;

use App\Actions\Event\UpdateEventAction;
use App\DTOs\Event\EventDTO;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateEventActionTest extends TestCase
{
    use RefreshDatabase;

    private function dto(Event $event, array $changes = []): EventDTO
    {
        return EventDTO::from(array_merge([
            'sport_id' => $event->sport_id,
            'title' => $event->title,
            'description' => $event->description,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => $event->duration_minutes,
            'city' => $event->city,
            'venue' => $event->venue,
            'capacity' => $event->capacity,
        ], $changes));
    }

    public function test_it_updates_the_event_fields(): void
    {
        $event = Event::factory()->create(['capacity' => 10]);

        (new UpdateEventAction())->execute($event, $this->dto($event, ['title' => 'New title', 'capacity' => 20]));

        $this->assertDatabaseHas('events', ['id' => $event->id, 'title' => 'New title', 'capacity' => 20]);
    }

    public function test_it_never_changes_the_organization(): void
    {
        $event = Event::factory()->create();
        $organizationId = $event->organization_id;
        $other = Organization::factory()->create();

        (new UpdateEventAction())->execute($event, $this->dto($event, ['organization_id' => $other->id]));

        $this->assertSame($organizationId, $event->fresh()->organization_id);
    }

    public function test_it_refuses_a_capacity_below_the_confirmed_registrations(): void
    {
        $event = Event::factory()->create(['capacity' => 10]);
        $event->participants()->attach(User::factory()->count(5)->create()->pluck('id'), ['status' => 'confirmed']);

        try {
            (new UpdateEventAction())->execute($event, $this->dto($event, ['capacity' => 4]));
            $this->fail('A ValidationException was expected.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('capacity', $e->errors());
        }

        $this->assertSame(10, $event->fresh()->capacity);
    }

    public function test_it_accepts_a_capacity_equal_to_the_confirmed_registrations(): void
    {
        $event = Event::factory()->create(['capacity' => 10]);
        $event->participants()->attach(User::factory()->count(5)->create()->pluck('id'), ['status' => 'confirmed']);

        (new UpdateEventAction())->execute($event, $this->dto($event, ['capacity' => 5]));

        $this->assertSame(5, $event->fresh()->capacity);
    }

    public function test_cancelled_registrations_do_not_count_for_the_capacity(): void
    {
        $event = Event::factory()->create(['capacity' => 10]);
        $event->participants()->attach(User::factory()->count(5)->create()->pluck('id'), ['status' => 'cancelled']);

        (new UpdateEventAction())->execute($event, $this->dto($event, ['capacity' => 2]));

        $this->assertSame(2, $event->fresh()->capacity);
    }
}
