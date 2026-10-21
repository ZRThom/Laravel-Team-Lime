<?php

namespace App\Http\Controllers;

use App\Actions\Event\CancelEventAction;
use App\Actions\Event\StoreEventAction;
use App\Actions\Event\UpdateEventAction;
use App\Http\Requests\Event\CancelEventRequest;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Sport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request): View
    {
        // If User can't create an event => Error 403
        $this->authorize('create', Event::class);

        $user = $request->user();

        // organizations where the user can create an event (an admin can pick any)
        $organizations = $user->is_admin
            ? Organization::all()
            : $user->organizations()->wherePivot('role', 'owner')->get(); // select a role for user

        $sports = Sport::where('active', true)->orderBy('name')->get();

        return view('events.create', compact('organizations', 'sports'));
    }

    public function store(StoreEventRequest $request, StoreEventAction $action): RedirectResponse
    {
        $event = $action->execute($request->validated(), $request->user());

        return redirect()->route('events.edit', $event)->with('status', __('Event created.'));
    }

    public function edit(Event $event): View
    {
        // If User is not owner => Error 403
        $this->authorize('update', $event);

        $sports = Sport::where('active', true)->orderBy('name')->get();

        return view('events.edit', compact('event', 'sports'));
    }

    public function update(UpdateEventRequest $request, Event $event, UpdateEventAction $action): RedirectResponse
    {
        $action->execute($event, $request->validated()); // event added with valid action

        return redirect()->route('events.edit', $event)->with('status', __('Event updated.'));
    }

    public function cancel(CancelEventRequest $request, Event $event, CancelEventAction $action): RedirectResponse
    {
        $action->execute($event);

        return redirect()->route('events.edit', $event)->with('status', __('Event cancelled.'));
    }
}
