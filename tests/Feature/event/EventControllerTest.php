<?php

namespace Tests\Feature\event;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    private function owner(Organization $organization): User
    {
        $user = User::factory()->create();
        $organization->users()->attach($user->id, ['role' => 'owner']);

        return $user;
    }

    private function payload(Organization $organization, array $changes = []): array
    {
        return array_merge([
            'organization_id' => $organization->id,
            'sport_id' => Sport::factory()->create()->id,
            'title' => 'Match du jeudi',
            'description' => 'Venez nombreux',
            'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'duration_minutes' => 90,
            'city' => 'Paris',
            'venue' => 'Stade Charlety',
            'capacity' => 10,
        ], $changes);
    }

    public function test_create_page_is_displayed_to_an_owner(): void
    {
        $organization = Organization::factory()->create();

        $this->actingAs($this->owner($organization))->get('/events/create')->assertOk()->assertSee($organization->name);
    }

    public function test_create_page_is_forbidden_to_a_user_who_owns_no_organization(): void
    {
        $this->actingAs(User::factory()->create())->get('/events/create')->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/events/create')->assertRedirect('/login');
        $this->post('/events')->assertRedirect('/login');
    }

    public function test_store_creates_a_published_event(): void
    {
        $organization = Organization::factory()->create();
        $owner = $this->owner($organization);

        $response = $this->actingAs($owner)->post('/events', $this->payload($organization));

        $event = Event::firstOrFail();
        $response->assertRedirect(route('events.edit', $event));
        $this->assertSame('published', $event->status);
        $this->assertSame($owner->id, $event->created_by);
        $this->assertSame(32, strlen($event->public_token));
    }

    public function test_store_validation_fails(): void
    {
        $organization = Organization::factory()->create();
        $owner = $this->owner($organization);

        $this->actingAs($owner)->post('/events', $this->payload($organization, [
            'title' => '',
            'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'duration_minutes' => 20,
            'capacity' => 1,
            'city' => '',
        ]))->assertSessionHasErrors(['title', 'starts_at', 'duration_minutes', 'capacity', 'city']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_store_duration_and_capacity_limits(): void
    {
        $organization = Organization::factory()->create();
        $owner = $this->owner($organization);

        foreach ([['duration_minutes' => 241], ['capacity' => 101], ['duration_minutes' => 29], ['capacity' => 1]] as $bad) {
            $this->actingAs($owner)->post('/events', $this->payload($organization, $bad))->assertSessionHasErrors(array_key_first($bad));
        }

        $this->actingAs($owner)->post('/events', $this->payload($organization, ['duration_minutes' => 30, 'capacity' => 2]))->assertSessionHasNoErrors();
        $this->actingAs($owner)->post('/events', $this->payload($organization, ['duration_minutes' => 240, 'capacity' => 100]))->assertSessionHasNoErrors();
    }

    public function test_store_refuses_an_organization_the_user_does_not_own(): void
    {
        $mine = Organization::factory()->create();
        $other = Organization::factory()->create();
        $owner = $this->owner($mine);

        $this->actingAs($owner)->post('/events', $this->payload($other))->assertSessionHasErrors('organization_id');

        $this->assertDatabaseCount('events', 0);
    }

    public function test_an_admin_can_create_an_event_in_any_organization(): void
    {
        $organization = Organization::factory()->create();

        $this->actingAs(User::factory()->admin()->create())->post('/events', $this->payload($organization))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('events', 1);
    }

    public function test_the_owner_can_update_the_event(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $owner = $this->owner($event->organization);

        $this->actingAs($owner)->get("/events/{$event->id}/edit")->assertOk()->assertSee($event->title);
        $this->actingAs($owner)->put("/events/{$event->id}", $this->payload($event->organization, ['title' => 'New title']))
            ->assertRedirect(route('events.edit', $event));

        $this->assertSame('New title', $event->fresh()->title);
    }

    public function test_update_is_refused_when_capacity_is_below_the_confirmed_registrations(): void
    {
        $event = Event::factory()->create(['capacity' => 10, 'starts_at' => now()->addDay()]);
        $event->participants()->attach(User::factory()->count(5)->create()->pluck('id'), ['status' => 'confirmed']);

        $this->actingAs($this->owner($event->organization))
            ->put("/events/{$event->id}", $this->payload($event->organization, ['capacity' => 3]))
            ->assertSessionHasErrors('capacity');

        $this->assertSame(10, $event->fresh()->capacity);
    }

    public function test_a_user_who_is_not_owner_cannot_edit_update_or_cancel(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get("/events/{$event->id}/edit")->assertForbidden();
        $this->actingAs($stranger)->put("/events/{$event->id}", $this->payload($event->organization))->assertForbidden();
        $this->actingAs($stranger)->patch("/events/{$event->id}/cancel")->assertForbidden();

        $this->assertSame('published', $event->fresh()->status);
    }

    public function test_an_admin_can_edit_and_cancel_any_event(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get("/events/{$event->id}/edit")->assertOk();
        $this->actingAs($admin)->patch("/events/{$event->id}/cancel")->assertRedirect();

        $this->assertSame('cancelled', $event->fresh()->status);
    }

    public function test_cancel_keeps_the_registrations(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $event->participants()->attach(User::factory()->count(3)->create()->pluck('id'), ['status' => 'confirmed']);

        $this->actingAs($this->owner($event->organization))->patch("/events/{$event->id}/cancel")->assertRedirect();

        $this->assertSame('cancelled', $event->fresh()->status);
        $this->assertDatabaseCount('event_participants', 3);
    }
}
