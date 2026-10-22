<?php

namespace Tests\Unit\mail;

use App\Mail\OwnerRegistrationNoticeMail;
use App\Mail\ParticipantRegistrationMail;
use App\Mail\RegistrationCancelledMail;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationMailsTest extends TestCase
{
    use RefreshDatabase;

    private function event(): Event
    {
        return Event::factory()->create(['title' => 'Match du jeudi', 'city' => 'Paris', 'venue' => 'Stade X', 'capacity' => 5, 'starts_at' => now()->addDay()->setTime(18, 30)]);
    }

    public function test_the_participant_mail_confirms_the_registration(): void
    {
        $event = $this->event();
        $participant = User::factory()->create(['name' => 'Alice Martin']);

        $mail = new ParticipantRegistrationMail($event, $participant);

        $mail->assertHasSubject('Your registration is confirmed: Match du jeudi');
        $mail->assertSeeInHtml('Alice Martin');
        $mail->assertSeeInHtml('Match du jeudi');
        $mail->assertSeeInHtml(now()->addDay()->format('d/m/Y').' 18:30');
        $mail->assertSeeInHtml('Stade X');
        $mail->assertSeeInHtml(route('events.public', $event->public_token));
    }

    public function test_the_owner_mail_gives_the_name_of_the_participant_and_the_event(): void
    {
        $event = $this->event();
        $participant = User::factory()->create(['name' => 'Alice Martin', 'email' => 'alice@example.com']);
        $event->participants()->attach($participant->id, ['status' => 'confirmed']);

        $mail = new OwnerRegistrationNoticeMail($event, $participant);

        $mail->assertHasSubject('Alice Martin registered to Match du jeudi');
        $mail->assertSeeInHtml('Alice Martin');
        $mail->assertSeeInHtml('alice@example.com');
        $mail->assertSeeInHtml('4 / 5');
        $mail->assertSeeInHtml(route('events.participants', $event));
    }

    public function test_the_cancellation_mail_confirms_the_cancellation(): void
    {
        $event = $this->event();
        $participant = User::factory()->create(['name' => 'Alice Martin']);

        $mail = new RegistrationCancelledMail($event, $participant);

        $mail->assertHasSubject('Your registration is cancelled: Match du jeudi');
        $mail->assertSeeInHtml('Alice Martin');
        $mail->assertSeeInHtml('Match du jeudi');
        $mail->assertSeeInHtml(route('events.public', $event->public_token));
    }
}
