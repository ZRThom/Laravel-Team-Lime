<?php

namespace Tests\Unit\event;

use App\Actions\Event\RegisterParticipantAction;
use App\DTOs\Event\RegistrationDTO;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegisterParticipantActionTest extends TestCase
{
    use RefreshDatabase;

    private function register(Event $event, User $user): void
    {
        (new RegisterParticipantAction())->execute(new RegistrationDTO($event->id, $user->id));
    }

    private function assertRefused(Event $event, User $user, string $message): void
    {
        try {
            $this->register($event, $user);
            $this->fail('A ValidationException was expected.');
        } catch (ValidationException $e) {
            $this->assertSame($message, $e->errors()['registration'][0]);
        }
    }

    public function test_it_confirms_the_registration_of_a_user(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $user = User::factory()->create();

        $this->register($event, $user);

        $this->assertDatabaseHas('event_participants', ['event_id' => $event->id, 'user_id' => $user->id, 'status' => 'confirmed']);
    }

    public function test_it_refuses_a_second_confirmed_registration_without_duplicate(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $user = User::factory()->create();
        $this->register($event, $user);

        $this->assertRefused($event, $user, 'You are already registered.');

        $this->assertDatabaseCount('event_participants', 1);
    }

    public function test_it_refuses_a_registration_when_the_event_is_full(): void
    {
        $event = Event::factory()->create(['capacity' => 2, 'starts_at' => now()->addDay()]);
        $this->register($event, User::factory()->create());
        $this->register($event, User::factory()->create());
        $late = User::factory()->create();

        $this->assertRefused($event, $late, 'This event is full.');

        $this->assertDatabaseMissing('event_participants', ['user_id' => $late->id]);
        $this->assertDatabaseCount('event_participants', 2);
    }

    public function test_a_cancelled_registration_does_not_take_a_seat(): void
    {
        $event = Event::factory()->create(['capacity' => 1, 'starts_at' => now()->addDay()]);
        $event->participants()->attach(User::factory()->create()->id, ['status' => 'cancelled']);
        $user = User::factory()->create();

        $this->register($event, $user);

        $this->assertDatabaseHas('event_participants', ['user_id' => $user->id, 'status' => 'confirmed']);
    }

    public function test_it_refuses_a_registration_to_a_closed_event(): void
    {
        $user = User::factory()->create();
        $closed = [
            Event::factory()->create(['starts_at' => now()->subHour()]),
            Event::factory()->draft()->create(['starts_at' => now()->addDay()]),
            Event::factory()->cancelled()->create(['starts_at' => now()->addDay()]),
        ];

        foreach ($closed as $event) {
            $this->assertRefused($event, $user, 'Registration is closed for this event.');
        }

        $this->assertDatabaseCount('event_participants', 0);
    }

    public function test_a_new_registration_after_a_cancellation_reactivates_the_same_line(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $user = User::factory()->create();
        $event->participants()->attach($user->id, ['status' => 'cancelled', 'registered_at' => now()->subDays(5)]);

        $this->register($event, $user);

        $this->assertDatabaseCount('event_participants', 1);
        $line = DB::table('event_participants')->first();
        $this->assertSame('confirmed', $line->status);
        $this->assertTrue(now()->subMinute()->lt($line->registered_at));
    }
}
