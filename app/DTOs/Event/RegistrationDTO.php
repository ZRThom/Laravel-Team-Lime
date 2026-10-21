<?php

namespace App\DTOs\Event;

use Spatie\LaravelData\Data;

class RegistrationDTO extends Data
{
    public function __construct(
        public int $event_id,
        public int $user_id,
    ) {}
}
