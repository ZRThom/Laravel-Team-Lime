<?php

namespace App\Actions\Event;

use App\Models\Event;

class CancelEventAction
{
    public function execute(Event $event): Event
    {
        // registrations are kept for the history, only the status changes
        $event->update(['status' => 'cancelled']);

        return $event;
    }
}
