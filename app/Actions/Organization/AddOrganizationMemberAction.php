<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;

class AddOrganizationMemberAction
{
    /**
     * Execute the action to add a member to an organization.
     * This class doesn't know how to add a member to an organization, it just defines the action.
     * The files focus only on the action itself, not the implementation details.
     */
    public function execute(Organization $organization, User $member): User
    {
        // Implementation of adding aa member to the organization goes here.
        // This could involve checking permissions, validating input, and updating the database.
        $organization->members()->syncWithoutDetaching([$member->id]);

        return $member;
    }
}
