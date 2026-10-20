<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrganizationPolicy
{
    // IsOwner method => rename in the future
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_admin) {
            return true;
        }

        return null; 
    }


    public function update(User $user, Organization $organization): bool
    {
        return $this->isOwner($user, $organization);
    }

 
    public function delete(User $user, Organization $organization): bool
    {
        return $this->isOwner($user, $organization);
    }


    private function isOwner(User $user, Organization $organization): bool
    {
        return DB::table('organization_user')
            ->where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}