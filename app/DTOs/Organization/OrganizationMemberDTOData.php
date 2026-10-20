<?php

namespace App\DTOS\Organization;

class OrganizationMemberDTOData extends Data
{
    public function __construct(
        public int $user_id,
        public string $role = 'member',
    ) {}
}

