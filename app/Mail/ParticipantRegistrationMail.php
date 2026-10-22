<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

// confirmation sent to the participant
class ParticipantRegistrationMail extends Mailable
{
    public function __construct(
        public Event $event,
        public User $participant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Your registration is confirmed: :title', ['title' => $this->event->title]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.events.participant-registration');
    }
}
