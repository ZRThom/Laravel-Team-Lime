<?php

namespace Tests\Unit\event;

use App\Actions\Event\CancelEventAction;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelEventActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_the_status_to_cancelled(): void
    {
        $event = Event::factory()->create();

        (new CancelEventAction())->execute($event);

        $this->assertSame('cancelled', $event->fresh()->status);
    }

    public function test_it_keeps_the_registrations_for_the_history(): void
    {
        $event = Event::factory()->create();
        $event->participants()->attach(User::factory()->count(3)->create()->pluck('id'), ['status' => 'confirmed']);

        (new CancelEventAction())->execute($event);

        $this->assertDatabaseCount('event_participants', 3);
        $this->assertDatabaseMissing('event_participants', ['status' => 'cancelled']);
    }
}
