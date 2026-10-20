<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class RemoveOrganizationMemberAction
{
    //* Remove a member from the organization.
    public function execute(Organization $organization, int $userId): void
    {
        $organization->members()->detach($userId);
    }

}
