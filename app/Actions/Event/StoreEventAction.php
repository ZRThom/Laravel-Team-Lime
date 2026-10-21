<?php

namespace App\Actions\Event;

use App\DTOs\Event\EventDTO;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Str;

class StoreEventAction
{
    public function execute(EventDTO $dto, User $creator): Event
    {
        // event is published right away, with an unguessable public token
        return Event::create($dto->toArray() + [
            'created_by' => $creator->id,
            'public_token' => Str::random(32),
            'status' => 'published',
        ]);
    }
}
