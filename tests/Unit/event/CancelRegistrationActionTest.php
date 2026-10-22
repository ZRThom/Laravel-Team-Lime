<?php

namespace Tests\Unit\event;

use App\Actions\Event\CancelRegistrationAction;
use App\DTOs\Event\RegistrationDTO;
use App\Events\RegistrationCancelled;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CancelRegistrationActionTest extends TestCase
{
    use RefreshDatabase;

    private function cancel(Event $event, User $user): void
    {
        (new CancelRegistrationAction())->execute(new RegistrationDTO($event->id, $user->id));
    }

    private function assertRefused(Event $event, User $user, string $message): void
    {
        try {
            $this->cancel($event, $user);
            $this->fail('A ValidationException was expected.');
        } catch (ValidationException $e) {
            $this->assertSame($message, $e->errors()['registration'][0]);
        }
    }

    public function test_it_cancels_the_registration_and_keeps_the_line(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $user = User::factory()->create();
        $event->participants()->attach($user->id, ['status' => 'confirmed']);

        $this->cancel($event, $user);

        $this->assertDatabaseCount('event_participants', 1);
        $this->assertDatabaseHas('event_participants', ['user_id' => $user->id, 'status' => 'cancelled']);
    }

    public function test_registration_cancelled_is_fired_after_a_cancellation(): void
    {
        EventFacade::fake([RegistrationCancelled::class]);
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $user = User::factory()->create();
        $event->participants()->attach($user->id, ['status' => 'confirmed']);

        $this->cancel($event, $user);

        EventFacade::assertDispatchedTimes(RegistrationCancelled::class, 1);
        EventFacade::assertDispatched(RegistrationCancelled::class, fn ($fired) => $fired->event->is($event) && $fired->participant->is($user));
    }

    public function test_registration_cancelled_is_not_fired_when_the_cancellation_is_refused(): void
    {
        EventFacade::fake([RegistrationCancelled::class]);
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $user = User::factory()->create();

        try {
            $this->cancel($event, $user);
        } catch (ValidationException) {
            // expected: no registration
        }

        EventFacade::assertNotDispatched(RegistrationCancelled::class);
    }

    public function test_it_only_cancels_the_registration_of_the_given_user(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        [$me, $other] = User::factory()->count(2)->create()->all();
        $event->participants()->attach([$me->id => ['status' => 'confirmed'], $other->id => ['status' => 'confirmed']]);

        $this->cancel($event, $me);

        $this->assertDatabaseHas('event_participants', ['user_id' => $other->id, 'status' => 'confirmed']);
    }

    public function test_it_refuses_to_cancel_after_the_start_of_the_event(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->subHour()]);
        $user = User::factory()->create();
        $event->participants()->attach($user->id, ['status' => 'confirmed']);

        $this->assertRefused($event, $user, 'You cannot cancel after the start of the event.');

        $this->assertDatabaseHas('event_participants', ['user_id' => $user->id, 'status' => 'confirmed']);
    }

    public function test_it_refuses_when_the_user_has_no_confirmed_registration(): void
    {
        $event = Event::factory()->create(['starts_at' => now()->addDay()]);
        $user = User::factory()->create();

        $this->assertRefused($event, $user, 'You have no registration for this event.');

        $event->participants()->attach($user->id, ['status' => 'cancelled']);
        $this->assertRefused($event, $user, 'You have no registration for this event.');
    }
}
