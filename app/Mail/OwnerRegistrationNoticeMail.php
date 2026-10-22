<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

// notice sent to the owner: someone booked a seat
class OwnerRegistrationNoticeMail extends Mailable
{
    public function __construct(
        public Event $event,
        public User $participant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __(':name registered to :title', ['name' => $this->participant->name, 'title' => $this->event->title]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.events.owner-registration-notice');
    }
}
