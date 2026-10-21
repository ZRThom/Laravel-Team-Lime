<?php

namespace App\Actions\Event;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Str;

class StoreEventAction
{
    public function execute(array $data, User $creator): Event
    {
        // event is published right away, with an unguessable public token
        return Event::create($data + [
            'created_by' => $creator->id,
            'public_token' => Str::random(32),
            'status' => 'published',
        ]);
    }
}
