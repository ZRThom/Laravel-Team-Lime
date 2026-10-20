<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreOrganizationAction
{
    public function execute(array $data, User $creator): Organization
    {
        return DB::transaction(function () use ($data, $creator) {
            $organization = Organization::create($data);

            $organization->members()->attach($creator->id, ['role' => 'owner']);

            return $organization;
        });
    }
}
