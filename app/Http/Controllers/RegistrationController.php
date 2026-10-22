<?php

namespace App\Http\Controllers;

use App\Actions\Event\CancelRegistrationAction;
use App\Actions\Event\RegisterParticipantAction;
use App\DTOs\Event\RegistrationDTO;
use App\Http\Requests\Event\RegisterParticipantRequest;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    use AuthorizesRequests;

    // confirmed participants of an event, for its owner
    public function index(Event $event): View
    {
        // If User is not owner => Error 403
        $this->authorize('viewParticipants', $event);

        $participants = $event->participants()->wherePivot('status', 'confirmed')->orderByPivot('registered_at')->get();

        return view('events.participants', compact('event', 'participants'));
    }

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
