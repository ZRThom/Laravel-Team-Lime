<?php

namespace Tests\Unit\listener;

use App\Events\ParticipantRegistered;
use App\Events\RegistrationCancelled;
use App\Listeners\SendCancellationEmail;
use App\Listeners\SendRegistrationEmails;
use App\Mail\OwnerRegistrationNoticeMail;
use App\Mail\ParticipantRegistrationMail;
use App\Mail\RegistrationCancelledMail;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationListenersTest extends TestCase
{
    use RefreshDatabase;

    private function attach(Event $event, string $role): User
    {
        $user = User::factory()->create();
        $event->organization->users()->attach($user->id, ['role' => $role]);

        return $user;
    }

    public function test_it_queues_a_confirmation_for_the_participant_and_a_notice_for_the_owner(): void
    {
        Mail::fake();
        $event = Event::factory()->create();
        $owner = $this->attach($event, 'owner');
        $participant = User::factory()->create();

        (new SendRegistrationEmails())->handle(new ParticipantRegistered($event, $participant));

        Mail::assertQueued(ParticipantRegistrationMail::class, fn ($mail) => $mail->hasTo($participant->email));
        Mail::assertQueued(OwnerRegistrationNoticeMail::class, fn ($mail) => $mail->hasTo($owner->email) && $mail->participant->is($participant));
        Mail::assertQueuedCount(2);
        Mail::assertNothingSent();
    }

    public function test_every_owner_is_notified_but_not_the_simple_members(): void
    {
        Mail::fake();
        $event = Event::factory()->create();
        $firstOwner = $this->attach($event, 'owner');
        $secondOwner = $this->attach($event, 'owner');
        $member = $this->attach($event, 'member');

        (new SendRegistrationEmails())->handle(new ParticipantRegistered($event, User::factory()->create()));

        Mail::assertQueued(OwnerRegistrationNoticeMail::class, 2);
        Mail::assertQueued(OwnerRegistrationNoticeMail::class, fn ($mail) => $mail->hasTo($firstOwner->email));
        Mail::assertQueued(OwnerRegistrationNoticeMail::class, fn ($mail) => $mail->hasTo($secondOwner->email));
        Mail::assertNotQueued(OwnerRegistrationNoticeMail::class, fn ($mail) => $mail->hasTo($member->email));
    }

    public function test_it_queues_a_cancellation_email_for_the_participant(): void
    {
        Mail::fake();
        $event = Event::factory()->create();
        $participant = User::factory()->create();

        (new SendCancellationEmail())->handle(new RegistrationCancelled($event, $participant));

        Mail::assertQueued(RegistrationCancelledMail::class, fn ($mail) => $mail->hasTo($participant->email));
        Mail::assertQueuedCount(1);
    }

    public function test_the_listeners_are_wired_to_the_events(): void
    {
        EventFacade::fake();

        EventFacade::assertListening(ParticipantRegistered::class, SendRegistrationEmails::class);
        EventFacade::assertListening(RegistrationCancelled::class, SendCancellationEmail::class);
    }
}
