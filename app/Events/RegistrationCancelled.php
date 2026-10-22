<?php

namespace App\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

// fired after a participant cancelled his registration
class RegistrationCancelled
{
    use Dispatchable;

    public function __construct(
        public Event $event,
        public User $participant,
    ) {}
}
