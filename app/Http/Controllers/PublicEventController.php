<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicEventController extends Controller
{
    public function show(Request $request, string $token): View
    {
        // unknown token, draft or cancelled event => 404
        $event = Event::with(['sport', 'organization'])
            ->where('public_token', $token)
            ->where('status', 'published')
            ->firstOrFail();

        // a visitor who logs in from this page comes back here
        if ($request->user() === null) {
            session(['url.intended' => url()->current()]);
        }

        return view('events.show', compact('event'));
    }
}
