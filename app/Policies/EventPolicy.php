<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }

    // an owner of at least one organization can create an event
    public function create(User $user): bool
    {
        return $user->organizations()->wherePivot('role', 'owner')->exists();
    }

    public function update(User $user, Event $event): bool
    {
        return $event->isOwnedBy($user);
    }

    public function cancel(User $user, Event $event): bool
    {
        return $event->isOwnedBy($user);
    }
}
