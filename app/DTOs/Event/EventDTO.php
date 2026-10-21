<?php

namespace App\DTOs\Event;

use Spatie\LaravelData\Data;

class EventDTO extends Data
{
    public function __construct(
        public int $sport_id,
        public string $title,
        public ?string $description,
        public string $starts_at,
        public int $duration_minutes,
        public string $city,
        public string $venue,
        public int $capacity,
        public ?int $organization_id = null, // only sent when creating (can't be changed after)
    ) {}
}
