<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // active organization chosen in the menu (kept in session)
        $organization = Organization::find(session('organization_id'));

        // only the owner of the organization (or an admin) sees its events
        $canManage = $organization !== null && $request->user()->can('update', $organization);

        $upcoming = $finished = $cancelled = collect();

        if ($canManage) {
            $events = Event::where('organization_id', $organization->id)
                ->with('sport')
                ->withConfirmedCount()
                ->orderBy('starts_at')
                ->get();

            $cancelled = $events->filter(fn ($event) => $event->status === 'cancelled');
            $upcoming = $events->filter(fn ($event) => $event->status === 'published' && $event->starts_at->isFuture());
            $finished = $events->filter(fn ($event) => $event->status === 'published' && $event->starts_at->isPast());
        }

        return view('dashboard.index', compact('organization', 'canManage', 'upcoming', 'finished', 'cancelled'));
    }
}
