<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetActiveOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $organizations = $user->organizations()->get();

            // keep the session choice only if the user is still a member, else take the first one
            $active = $organizations->firstWhere('id', session('organization_id')) ?? $organizations->first();

            session(['organization_id' => $active?->id]);

            // available in every view (used by the menu)
            View::share('userOrganizations', $organizations);
            View::share('activeOrganization', $active);
        }

        return $next($request);
    }
}
