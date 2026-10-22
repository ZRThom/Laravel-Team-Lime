<?php

namespace Tests\Feature\event;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_displayed_to_a_visitor(): void
    {
        $this->get('/')->assertOk()->assertSee('Log in');
    }

    public function test_home_page_lists_only_published_future_events_with_a_free_seat(): void
    {
        Event::factory()->create(['title' => 'Visible event', 'starts_at' => now()->addDay()]);
        Event::factory()->create(['title' => 'Past event', 'starts_at' => now()->subDay()]);
        Event::factory()->draft()->create(['title' => 'Draft event', 'starts_at' => now()->addDay()]);
        Event::factory()->cancelled()->create(['title' => 'Cancelled event', 'starts_at' => now()->addDay()]);
        $full = Event::factory()->create(['title' => 'Full event', 'capacity' => 2, 'starts_at' => now()->addDay()]);
        $full->participants()->attach(User::factory()->count(2)->create()->pluck('id'), ['status' => 'confirmed']);

        $this->get('/')->assertOk()
            ->assertSee('Visible event')
            ->assertDontSee('Past event')
            ->assertDontSee('Draft event')
            ->assertDontSee('Cancelled event')
            ->assertDontSee('Full event');
    }

    public function test_events_are_sorted_by_date_ascending(): void
    {
        Event::factory()->create(['title' => 'Later event', 'starts_at' => now()->addDays(5)]);
        Event::factory()->create(['title' => 'Sooner event', 'starts_at' => now()->addDays(1)]);

        $this->get('/')->assertSeeInOrder(['Sooner event', 'Later event']);
    }

    public function test_card_shows_sport_title_date_city_seats_and_organization(): void
    {
        $event = Event::factory()->create([
            'title' => 'Match du jeudi',
            'city' => 'Paris',
            'capacity' => 5,
            'starts_at' => now()->addDay()->setTime(18, 30),
            'sport_id' => Sport::factory()->create(['name' => 'Footix'])->id,
            'organization_id' => Organization::factory()->create(['name' => 'Collectif Lime'])->id,
        ]);
        $event->participants()->attach(User::factory()->create()->id, ['status' => 'confirmed']);

        $this->get('/')->assertSee('Footix')->assertSee('Match du jeudi')->assertSee('Paris')
            ->assertSee(now()->addDay()->format('d/m/Y').' 18:30')
            ->assertSee('4 seats left')
            ->assertSee('Collectif Lime')
            ->assertSee(route('events.public', $event->public_token));
    }

    public function test_filters_can_be_combined(): void
    {
        $football = Sport::factory()->create();
        Event::factory()->create(['title' => 'Foot Paris', 'sport_id' => $football->id, 'city' => 'Paris', 'starts_at' => now()->addDays(2)]);
        Event::factory()->create(['title' => 'Tennis Lyon', 'city' => 'Lyon', 'starts_at' => now()->addDays(3)]);

        $this->get('/?sport_id='.$football->id)->assertSee('Foot Paris')->assertDontSee('Tennis Lyon');
        $this->get('/?city=lyo')->assertSee('Tennis Lyon')->assertDontSee('Foot Paris');
        $this->get('/?date='.now()->addDays(3)->toDateString())->assertSee('Tennis Lyon')->assertDontSee('Foot Paris');
        $this->get('/?sport_id='.$football->id.'&city=Lyon')->assertDontSee('Foot Paris')->assertDontSee('Tennis Lyon');
    }

    public function test_clearing_the_filters_shows_the_whole_list_again(): void
    {
        Event::factory()->create(['title' => 'Foot Paris', 'city' => 'Paris', 'starts_at' => now()->addDays(2)]);
        Event::factory()->create(['title' => 'Tennis Lyon', 'city' => 'Lyon', 'starts_at' => now()->addDays(3)]);

        $this->get('/?sport_id=&city=&date=')->assertSee('Foot Paris')->assertSee('Tennis Lyon');
    }

    public function test_a_search_without_result_shows_an_empty_state(): void
    {
        Event::factory()->create(['city' => 'Paris', 'starts_at' => now()->addDay()]);

        $this->get('/?city=Marseille')->assertOk()->assertSee('No event matches your search.');
    }

    public function test_results_are_paginated_by_12_and_keep_the_filters(): void
    {
        Event::factory()->count(15)->create(['city' => 'Nantes', 'starts_at' => now()->addDay()]);
        Event::factory()->count(3)->create(['city' => 'Other', 'starts_at' => now()->addDay()]);

        $first = $this->get('/?city=Nantes')->assertOk();
        $this->assertSame(12, substr_count($first->getContent(), 'href="'.url('/e/')));
        $first->assertSee('city=Nantes', false)->assertSee('page=2', false);

        $second = $this->get('/?city=Nantes&page=2');
        $this->assertSame(3, substr_count($second->getContent(), 'href="'.url('/e/')));
    }
}
