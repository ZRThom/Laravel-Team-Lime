<?php

namespace App\Http\Controllers;

use App\Actions\Event\CancelRegistrationAction;
use App\Actions\Event\RegisterParticipantAction;
use App\DTOs\Event\RegistrationDTO;
use App\Http\Requests\Event\RegisterParticipantRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function store(RegisterParticipantRequest $request, Event $event, RegisterParticipantAction $action): RedirectResponse
    {
        // the user is always the connected one
        $action->execute(new RegistrationDTO($event->id, $request->user()->id));

        return redirect()->route('events.public', $event->public_token)->with('status', __('You are registered.'));
    }

    public function destroy(Request $request, Event $event, CancelRegistrationAction $action): RedirectResponse
    {
        // only the connected user's own registration can be cancelled
        $action->execute(new RegistrationDTO($event->id, $request->user()->id));

        return redirect()->route('events.public', $event->public_token)->with('status', __('Your registration is cancelled.'));
    }
}
