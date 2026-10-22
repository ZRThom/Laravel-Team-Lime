<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

// There is no Registration model (it is the event_participants table),
// so the abilities are registered by name in AppServiceProvider: 'registration.cancel', ...
class RegistrationPolicy
{
    // list of the participants of an event: owner of the organization or admin
    public function viewAny(User $user, Event $event): bool
    {
        return $user->is_admin || $event->isOwnedBy($user);
    }

    // a user sees only his own registration
    public function view(User $user, Event $event): bool
    {
        return $this->hasRegistration($user, $event);
    }

    // any connected user can participate (the rules of the event are checked by the action)
    public function create(User $user, Event $event): bool
    {
        return true;
    }

    // a user cancels only his own registration
    public function cancel(User $user, Event $event): bool
    {
        return $this->hasRegistration($user, $event);
    }

    private function hasRegistration(User $user, Event $event): bool
    {
        return $user->events()->whereKey($event->id)->exists();
    }
}
