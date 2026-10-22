<?php

namespace Tests\Feature\event;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    private function owner(Event $event): User
    {
        $user = User::factory()->create();
        $event->organization->users()->attach($user->id, ['role' => 'owner']);

        return $user;
    }

    public function test_the_owner_sees_the_management_screen_with_the_public_link(): void
    {
        $event = Event::factory()->create(['title' => 'Match du jeudi', 'capacity' => 10, 'starts_at' => now()->addDay()]);
        $event->participants()->attach(User::factory()->count(2)->create()->pluck('id'), ['status' => 'confirmed']);

        $this->actingAs($this->owner($event))->get("/events/{$event->id}/manage")->assertOk()
            ->assertSee('Match du jeudi')
            ->assertSee('2 / 10')
            ->assertSee(route('events.public', $event->public_token))
            ->assertSee('Copy')
            ->assertSee(route('events.participants', $event));
    }

    public function test_the_participants_page_lists_only_confirmed_participants_with_name_email_and_date(): void
    {
        $event = Event::factory()->create(['capacity' => 10, 'starts_at' => now()->addDay()]);
        $confirmed = User::factory()->create(['name' => 'Alice Martin', 'email' => 'alice@example.com']);
        $cancelled = User::factory()->create(['name' => 'Bob Cancelled', 'email' => 'bob@example.com']);
        $event->participants()->attach([
            $confirmed->id => ['status' => 'confirmed', 'registered_at' => '2026-10-20 14:05:00'],
            $cancelled->id => ['status' => 'cancelled', 'registered_at' => '2026-10-20 15:00:00'],
        ]);

        $this->actingAs($this->owner($event))->get("/events/{$event->id}/participants")->assertOk()
            ->assertSee('Alice Martin')
            ->assertSee('alice@example.com')
            ->assertSee('20/10/2026 14:05')
            ->assertSee('1 / 10')
            ->assertDontSee('Bob Cancelled')
            ->assertDontSee('bob@example.com');
    }

    public function test_the_participants_page_shows_an_empty_message(): void
    {
        $event = Event::factory()->create();

        $this->actingAs($this->owner($event))->get("/events/{$event->id}/participants")->assertOk()->assertSee('No participant yet.');
    }

    public function test_other_users_get_a_403_on_both_pages(): void
    {
        $event = Event::factory()->create();
        $member = User::factory()->create();
        $event->organization->users()->attach($member->id, ['role' => 'member']);

        foreach ([User::factory()->create(), $member] as $user) {
            $this->actingAs($user)->get("/events/{$event->id}/manage")->assertForbidden();
            $this->actingAs($user)->get("/events/{$event->id}/participants")->assertForbidden();
        }
    }

    public function test_the_owner_of_another_organization_gets_a_403(): void
    {
        $event = Event::factory()->create();
        $otherEvent = Event::factory()->create();

        $this->actingAs($this->owner($otherEvent))->get("/events/{$event->id}/participants")->assertForbidden();
    }

    public function test_an_admin_can_open_both_pages_for_any_event(): void
    {
        $event = Event::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get("/events/{$event->id}/manage")->assertOk();
        $this->actingAs($admin)->get("/events/{$event->id}/participants")->assertOk();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $event = Event::factory()->create();

        $this->get("/events/{$event->id}/manage")->assertRedirect('/login');
        $this->get("/events/{$event->id}/participants")->assertRedirect('/login');
    }
}
