<?php

namespace Tests\Feature\dashboard;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): array
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => $role]);

        return [$user, $organization];
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_a_user_without_organization_sees_a_message(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()->assertSee('No organization yet.');
    }

    public function test_a_simple_member_does_not_see_the_events(): void
    {
        [$member, $organization] = $this->userWithRole('member');
        Event::factory()->create(['organization_id' => $organization->id, 'title' => 'Secret event', 'starts_at' => now()->addDay()]);

        $this->actingAs($member)->get('/dashboard')->assertOk()
            ->assertSee('Only owners can see the events of an organization.')
            ->assertDontSee('Secret event');
    }

    public function test_the_owner_sees_the_events_split_in_upcoming_finished_and_cancelled(): void
    {
        [$owner, $organization] = $this->userWithRole('owner');
        $make = fn (array $attributes) => Event::factory()->create($attributes + ['organization_id' => $organization->id]);
        $make(['title' => 'Upcoming one', 'starts_at' => now()->addDay()]);
        $make(['title' => 'Finished one', 'starts_at' => now()->subDay()]);
        Event::factory()->cancelled()->create(['organization_id' => $organization->id, 'title' => 'Cancelled one', 'starts_at' => now()->addDay()]);

        $response = $this->actingAs($owner)->get('/dashboard')->assertOk();

        $response->assertSeeInOrder(['Upcoming events', 'Upcoming one', 'Finished events', 'Finished one', 'Cancelled events', 'Cancelled one']);
    }

    public function test_each_row_shows_sport_date_city_status_and_registered_on_capacity(): void
    {
        [$owner, $organization] = $this->userWithRole('owner');
        $event = Event::factory()->create([
            'organization_id' => $organization->id,
            'sport_id' => Sport::factory()->create(['name' => 'Footix'])->id,
            'city' => 'Paris',
            'capacity' => 10,
            'starts_at' => now()->addDay()->setTime(18, 30),
        ]);
        $users = User::factory()->count(3)->create();
        $event->participants()->attach([
            $users[0]->id => ['status' => 'confirmed'],
            $users[1]->id => ['status' => 'confirmed'],
            $users[2]->id => ['status' => 'cancelled'],
        ]);

        $this->actingAs($owner)->get('/dashboard')->assertOk()
            ->assertSee('Footix')
            ->assertSee(now()->addDay()->format('d/m/Y').' 18:30')
            ->assertSee('Paris')
            ->assertSee('published')
            ->assertSee('2 / 10')
            ->assertSee(route('events.manage', $event));
    }

    public function test_events_of_another_organization_are_not_listed(): void
    {
        [$owner] = $this->userWithRole('owner');
        Event::factory()->create(['title' => 'Foreign event', 'starts_at' => now()->addDay()]);

        $this->actingAs($owner)->get('/dashboard')->assertOk()->assertDontSee('Foreign event');
    }

    public function test_the_dashboard_follows_the_active_organization(): void
    {
        [$owner, $first] = $this->userWithRole('owner');
        $second = Organization::factory()->create();
        $second->users()->attach($owner->id, ['role' => 'owner']);
        Event::factory()->create(['organization_id' => $first->id, 'title' => 'First org event', 'starts_at' => now()->addDay()]);
        Event::factory()->create(['organization_id' => $second->id, 'title' => 'Second org event', 'starts_at' => now()->addDay()]);

        $this->actingAs($owner)->post("/organizations/{$second->id}/switch");

        $this->get('/dashboard')->assertSee('Second org event')->assertDontSee('First org event');
    }
}
