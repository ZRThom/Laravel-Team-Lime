<?php

namespace App\Actions\Event;

use App\Models\Event;
use Illuminate\Validation\ValidationException;

class UpdateEventAction
{
    public function execute(Event $event, array $data): Event
    {
        // capacity can't go below the registrations already confirmed
        $confirmed = $event->participants()->wherePivot('status', 'confirmed')->count();

        if (isset($data['capacity']) && $data['capacity'] < $confirmed) {
            throw ValidationException::withMessages([
                'capacity' => __('Capacity cannot be lower than the :count confirmed registrations.', ['count' => $confirmed]),
            ]);
        }

        $event->update($data);

        return $event;
    }
}
