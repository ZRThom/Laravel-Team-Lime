<?php

namespace App\DTOs\Organization;

use Spatie\LaravelData\Data;

class OrganizationDTOData extends Data
{
    public function __construct(
        public int $user_id,
        public string $role = 'member',
    ) {}
}
