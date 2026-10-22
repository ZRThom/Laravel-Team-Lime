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

    // the public list is open to everybody, visitors included
    public function viewAny(?User $user): bool
    {
        return true;
    }

    // a published event is public, the others are for the members of the organization
    public function view(?User $user, Event $event): bool
    {
        if ($event->status === 'published') {
            return true;
        }

        return $user !== null && $user->roleIn($event->organization) !== null;
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
