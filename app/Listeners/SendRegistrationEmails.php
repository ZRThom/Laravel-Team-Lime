<?php

namespace App\Listeners;

use App\Events\ParticipantRegistered;
use App\Mail\OwnerRegistrationNoticeMail;
use App\Mail\ParticipantRegistrationMail;
use Illuminate\Support\Facades\Mail;

class SendRegistrationEmails
{
    // the mails are only put in the queue here, they are sent later by "php artisan queue:work",
    // so a mail transport error can never remove the registration
    public function handle(ParticipantRegistered $registered): void
    {
        $event = $registered->event;
        $participant = $registered->participant;

        Mail::to($participant)->queue(new ParticipantRegistrationMail($event, $participant));

        $owners = $event->organization->users()->wherePivot('role', 'owner')->get();

        foreach ($owners as $owner) {
            Mail::to($owner)->queue(new OwnerRegistrationNoticeMail($event, $participant));
        }
    }
}
