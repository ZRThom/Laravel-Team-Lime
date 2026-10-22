<?php

namespace Tests\Feature\event;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);

        $this->post("/events/{$event->id}/registration")->assertRedirect('/login');
        $this->delete("/events/{$event->id}/registration")->assertRedirect('/login');
    }

    public function test_a_user_can_register_and_the_seats_go_down(): void
    {
        $event = Event::factory()->create(['capacity' => 3, 'starts_at' => now()->addDay()]);
        $user = User::factory()->create();

        $this->actingAs($user)->post("/events/{$event->id}/registration")
            ->assertRedirect(route('events.public', $event->public_token))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_participants', ['event_id' => $event->id, 'user_id' => $user->id, 'status' => 'confirmed']);
        $this->actingAs($user)->get('/e/'.$event->public_token)
            ->assertSee('You are registered.')->assertSee('Cancel my registration')->assertSeeInOrder(['Seats left', '2']);
    }

    public function test_a_second_registration_returns_an_explicit_message_without_duplicate(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $user = User::factory()->create();
        $event->participants()->attach($user->id, ['status' => 'confirmed']);

        $this->actingAs($user)->post("/events/{$event->id}/registration")
            ->assertSessionHasErrors(['registration' => 'You are already registered.']);

        $this->assertDatabaseCount('event_participants', 1);
    }

    public function test_registration_is_refused_when_the_event_is_full(): void
    {
        $event = Event::factory()->create(['capacity' => 2, 'starts_at' => now()->addDay()]);
        $event->participants()->attach(User::factory()->count(2)->create()->pluck('id'), ['status' => 'confirmed']);
        $late = User::factory()->create();

        $this->actingAs($late)->post("/events/{$event->id}/registration")
            ->assertSessionHasErrors(['registration' => 'This event is full.']);

        $this->assertDatabaseMissing('event_participants', ['user_id' => $late->id]);
    }

    public function test_registration_is_refused_for_a_closed_event(): void
    {
        $user = User::factory()->create();

        foreach ([
            Event::factory()->create(['starts_at' => now()->subHour()]),
            Event::factory()->draft()->create(['starts_at' => now()->addDay()]),
            Event::factory()->cancelled()->create(['starts_at' => now()->addDay()]),
        ] as $event) {
            $this->actingAs($user)->post("/events/{$event->id}/registration")
                ->assertSessionHasErrors(['registration' => 'Registration is closed for this event.']);
        }

        $this->assertDatabaseCount('event_participants', 0);
    }

    public function test_a_participant_can_cancel_his_registration_and_the_seats_go_up(): void
    {
        $event = Event::factory()->create(['capacity' => 2, 'starts_at' => now()->addDay()]);
        $user = User::factory()->create();
        $event->participants()->attach($user->id, ['status' => 'confirmed']);

        $this->actingAs($user)->delete("/events/{$event->id}/registration")
            ->assertRedirect(route('events.public', $event->public_token))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_participants', ['user_id' => $user->id, 'status' => 'cancelled']);
        $this->actingAs($user)->get('/e/'.$event->public_token)->assertSeeInOrder(['Seats left', '2'])->assertSee('Participate');
    }

    public function test_a_user_cannot_cancel_the_registration_of_someone_else(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        [$participant, $stranger] = User::factory()->count(2)->create()->all();
        $event->participants()->attach($participant->id, ['status' => 'confirmed']);

        $this->actingAs($stranger)->delete("/events/{$event->id}/registration")
            ->assertSessionHasErrors('registration');

        $this->assertDatabaseHas('event_participants', ['user_id' => $participant->id, 'status' => 'confirmed']);
    }

    public function test_cancel_is_refused_after_the_start_of_the_event(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->subHour()]);
        $user = User::factory()->create();
        $event->participants()->attach($user->id, ['status' => 'confirmed']);

        $this->actingAs($user)->delete("/events/{$event->id}/registration")
            ->assertSessionHasErrors(['registration' => 'You cannot cancel after the start of the event.']);

        $this->assertDatabaseHas('event_participants', ['user_id' => $user->id, 'status' => 'confirmed']);
    }

    public function test_registering_again_after_a_cancellation_reuses_the_same_line(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $user = User::factory()->create();
        $event->participants()->attach($user->id, ['status' => 'cancelled', 'registered_at' => now()->subDays(5)]);

        $this->actingAs($user)->post("/events/{$event->id}/registration")->assertSessionHasNoErrors();

        $this->assertDatabaseCount('event_participants', 1);
        $this->assertDatabaseHas('event_participants', ['user_id' => $user->id, 'status' => 'confirmed']);
        $this->assertTrue($user->events()->first()->pivot->registered_at > now()->subMinute());
    }
}
