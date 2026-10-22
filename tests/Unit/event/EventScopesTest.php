<?php

namespace Tests\Unit\event;

use App\Models\Event;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_upcoming_keeps_only_published_future_events_with_a_free_seat(): void
    {
        $visible = Event::factory()->create(['starts_at' => now()->addDay()]);
        Event::factory()->create(['starts_at' => now()->subDay()]);
        Event::factory()->draft()->create(['starts_at' => now()->addDay()]);
        Event::factory()->cancelled()->create(['starts_at' => now()->addDay()]);
        $full = Event::factory()->create(['capacity' => 2, 'starts_at' => now()->addDay()]);
        $full->participants()->attach(User::factory()->count(2)->create()->pluck('id'), ['status' => 'confirmed']);

        $this->assertSame([$visible->id], Event::upcoming()->pluck('id')->all());
    }

    public function test_cancelled_registrations_do_not_make_an_event_full(): void
    {
        $event = Event::factory()->create(['capacity' => 2, 'starts_at' => now()->addDay()]);
        $event->participants()->attach(User::factory()->count(2)->create()->pluck('id'), ['status' => 'cancelled']);

        $this->assertSame([$event->id], Event::upcoming()->pluck('id')->all());
    }

    public function test_filter_by_sport_city_and_date_can_be_combined(): void
    {
        $football = Sport::factory()->create();
        $paris = Event::factory()->create(['sport_id' => $football->id, 'city' => 'Paris', 'starts_at' => now()->addDays(2)->setTime(18, 0)]);
        $lyon = Event::factory()->create(['city' => 'Lyon', 'starts_at' => now()->addDays(3)]);

        $this->assertSame([$paris->id], Event::filter(['sport_id' => $football->id])->pluck('id')->all());
        $this->assertSame([$lyon->id], Event::filter(['city' => 'lyo'])->pluck('id')->all());
        $this->assertSame([$paris->id], Event::filter(['date' => $paris->starts_at->toDateString()])->pluck('id')->all());
        $this->assertSame([], Event::filter(['sport_id' => $football->id, 'city' => 'Lyon'])->pluck('id')->all());
    }

    public function test_empty_filters_return_every_event(): void
    {
        Event::factory()->count(3)->create();

        $this->assertCount(3, Event::filter([])->get());
        $this->assertCount(3, Event::filter(['sport_id' => '', 'city' => '', 'date' => ''])->get());
    }

    public function test_remaining_seats_count_only_confirmed_registrations(): void
    {
        $event = Event::factory()->create(['capacity' => 5]);
        $users = User::factory()->count(3)->create();
        $event->participants()->attach([
            $users[0]->id => ['status' => 'confirmed'],
            $users[1]->id => ['status' => 'confirmed'],
            $users[2]->id => ['status' => 'cancelled'],
        ]);

        $this->assertSame(3, $event->remainingSeats());
        $this->assertSame(3, Event::withConfirmedCount()->find($event->id)->remainingSeats());
    }
}
