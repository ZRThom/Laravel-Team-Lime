<?php

namespace App\Actions\Event;

use App\DTOs\Event\EventDTO;
use App\Models\Event;
use Illuminate\Validation\ValidationException;

class UpdateEventAction
{
    public function execute(Event $event, EventDTO $dto): Event
    {
        // capacity can't go below the registrations already confirmed
        $confirmed = $event->participants()->wherePivot('status', 'confirmed')->count();

        if ($dto->capacity < $confirmed) {
            throw ValidationException::withMessages([
                'capacity' => __('Capacity cannot be lower than the :count confirmed registrations.', ['count' => $confirmed]),
            ]);
        }

        // the organization of an event never changes
        $event->update($dto->except('organization_id')->toArray());

        return $event;
    }
}
