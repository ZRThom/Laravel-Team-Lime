<?php

namespace App\Actions\Event;

use App\DTOs\Event\RegistrationDTO;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterParticipantAction
{
    public function execute(RegistrationDTO $dto): void
    {
        // capacity check and insert in one transaction, the event row is locked meanwhile
        DB::transaction(function () use ($dto) {
            $event = Event::lockForUpdate()->findOrFail($dto->event_id);

            if ($event->status !== 'published' || $event->starts_at->isPast()) {
                $this->fail(__('Registration is closed for this event.'));
            }

            // the registration line of this user, if he already had one
            $existing = $event->participants()->where('users.id', $dto->user_id)->first();

            if ($existing && $existing->pivot->status === 'confirmed') {
                $this->fail(__('You are already registered.'));
            }

            if ($event->remainingSeats() <= 0) {
                $this->fail(__('This event is full.'));
            }

            $line = ['status' => 'confirmed', 'registered_at' => now()];

            if ($existing) {
                // after a cancellation, the same line is reactivated
                $event->participants()->updateExistingPivot($dto->user_id, $line);
            } else {
                $event->participants()->attach($dto->user_id, $line);
            }
        });
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['registration' => $message]);
    }
}
