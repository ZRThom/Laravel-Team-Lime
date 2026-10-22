<?php

namespace App\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

// fired once, after a registration has been confirmed
class ParticipantRegistered
{
    use Dispatchable;

    public function __construct(
        public Event $event,
        public User $participant,
    ) {}
}
