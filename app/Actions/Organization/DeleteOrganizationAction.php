<?php

namespace App\Actions\Organization;

use App\Models\Organization;

class DeleteOrganizationAction
{
    /**
     * Execute the action to delete a member to an organization.
     * This class doesn't know how to delete a member to an organization, it just defines the action.
     * The files focus only on the action itself, not the implementation details.
    */
    public function execute(Organization $organization): void
    {
        $organization->delete();
        

    }
}
