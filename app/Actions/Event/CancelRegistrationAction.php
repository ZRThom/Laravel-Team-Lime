<?php

namespace App\Actions\Event;

use App\DTOs\Event\RegistrationDTO;
use App\Models\Event;
use Illuminate\Validation\ValidationException;

class CancelRegistrationAction
{
    public function execute(RegistrationDTO $dto): void
    {
        $event = Event::findOrFail($dto->event_id);

        if ($event->starts_at->isPast()) {
            $this->fail(__('You cannot cancel after the start of the event.'));
        }

        $registered = $event->participants()
            ->where('users.id', $dto->user_id)
            ->wherePivot('status', 'confirmed')
            ->exists();

        if (! $registered) {
            $this->fail(__('You have no registration for this event.'));
        }

        // the line is kept, only the status changes
        $event->participants()->updateExistingPivot($dto->user_id, ['status' => 'cancelled']);
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['registration' => $message]);
    }
}
