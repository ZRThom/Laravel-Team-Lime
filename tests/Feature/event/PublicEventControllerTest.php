<?php

namespace Tests\Feature\event;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_sees_all_the_details_of_a_published_event(): void
    {
        $event = Event::factory()->create([
            'title' => 'Match public',
            'description' => 'Une description',
            'city' => 'Paris',
            'venue' => 'Stade X',
            'capacity' => 5,
            'duration_minutes' => 90,
            'starts_at' => now()->addDays(2)->setTime(18, 30),
            'sport_id' => Sport::factory()->create(['name' => 'Footix'])->id,
            'organization_id' => Organization::factory()->create(['name' => 'Collectif Lime'])->id,
        ]);
        $event->participants()->attach(User::factory()->create()->id, ['status' => 'confirmed']);

        $this->get('/e/'.$event->public_token)->assertOk()
            ->assertSee('Match public')->assertSee('Footix')->assertSee('Collectif Lime')
            ->assertSee(now()->addDays(2)->format('d/m/Y').' 18:30')
            ->assertSee('90 min')->assertSee('Paris')->assertSee('Stade X')->assertSee('Une description')
            ->assertSeeInOrder(['Capacity', '5', 'Seats left', '4']);
    }

    public function test_an_unknown_token_returns_404(): void
    {
        $this->get('/e/unknown-token')->assertNotFound();
    }

    public function test_a_cancelled_event_returns_404(): void
    {
        $event = Event::factory()->cancelled()->create();

        $this->get('/e/'.$event->public_token)->assertNotFound();
    }

    public function test_a_draft_event_returns_404(): void
    {
        $event = Event::factory()->draft()->create();

        $this->get('/e/'.$event->public_token)->assertNotFound();
    }

    public function test_a_visitor_is_invited_to_log_in_or_register_to_participate(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);

        $this->get('/e/'.$event->public_token)->assertOk()
            ->assertSee('please log in or create an account')
            ->assertSee(route('login'))
            ->assertSee(route('register'));

        $this->assertSame(url('/e/'.$event->public_token), session('url.intended'));
    }

    public function test_a_logged_in_user_sees_the_participate_button(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);

        $this->actingAs(User::factory()->create())->get('/e/'.$event->public_token)->assertOk()
            ->assertSee('Participate')->assertDontSee('please log in');
    }

    public function test_a_full_event_shows_full_instead_of_the_button(): void
    {
        $event = Event::factory()->create(['capacity' => 2, 'starts_at' => now()->addDay()]);
        $event->participants()->attach(User::factory()->count(2)->create()->pluck('id'), ['status' => 'confirmed']);

        $this->actingAs(User::factory()->create())->get('/e/'.$event->public_token)->assertOk()
            ->assertSee('Full')->assertDontSee('Participate');
    }

    public function test_a_started_event_has_no_button(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->subHour()]);

        $this->actingAs(User::factory()->create())->get('/e/'.$event->public_token)->assertOk()
            ->assertDontSee('Participate')->assertDontSee('please log in');
    }
}
