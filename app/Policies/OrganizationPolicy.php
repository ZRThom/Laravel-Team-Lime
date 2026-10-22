<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    // an admin can do everything
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_admin) {
            return true;
        }

        return null;
    }

    // the list is filtered by the controller (only my organizations)
    public function viewAny(User $user): bool
    {
        return true;
    }

    // a member or an owner of the organization can see it
    public function view(User $user, Organization $organization): bool
    {
        return $user->roleIn($organization) !== null;
    }

    // any connected user can create an organization (and becomes its owner)
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->roleIn($organization) === 'owner';
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->roleIn($organization) === 'owner';
    }
}
