<?php

namespace App\Http\Controllers;

use App\Model\Organization;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::all();

        return view('organizations', compact('organizations'));
    }

    public function edit(Organization $organization)
    {
        // If User is not owner => Error 403
        $this->authorize('update', $organization);

        return view('organizations.edit', compact('organization'));
    }
}
