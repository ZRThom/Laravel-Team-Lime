<?php

namespace App\Http\Controllers;

use App\Actions\Organization\AddOrganizationMemberAction;
use App\Actions\Organization\DeleteOrganizationAction;
use App\Actions\Organization\RemoveOrganizationMemberAction;
use App\Actions\Organization\StoreOrganizationAction;
use App\Actions\Organization\UpdateOrganizationAction;
use App\Http\Requests\Organization\AddOrganizationRequestMember;
use App\Http\Requests\Organization\DeleteOrganization;
use App\Http\Requests\Organization\RemoveOrganizationMemberRequest;
use App\Http\Requests\Organization\StoreOrganization;
use App\Http\Requests\Organization\UpdateOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $organizations = Organization::all();

        return view('organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        return view('organizations.create');
    }

    public function store(StoreOrganization $request, StoreOrganizationAction $action): RedirectResponse
    {
        $action->execute($request->validated(), $request->user());

        return redirect()->route('organizations.index')->with('status', __('Organization created.'));
    }

    public function edit(Organization $organization): View
    {
        // If User is not owner => Error 403
        $this->authorize('update', $organization);

        $organization->load('users');

        return view('organizations.edit', compact('organization'));
    }

    public function update(UpdateOrganization $request, Organization $organization, UpdateOrganizationAction $action): RedirectResponse
    {
        $action->execute($organization, $request->validated());

        return redirect()->route('organizations.edit', $organization)->with('status', 'organization-updated');
    }

    public function destroy(DeleteOrganization $request, Organization $organization, DeleteOrganizationAction $action): RedirectResponse
    {
        $action->execute($organization);

        return redirect()->route('organizations.index')->with('status', __('Organization deleted.'));
    }

    public function addMember(AddOrganizationRequestMember $request, Organization $organization, AddOrganizationMemberAction $action): RedirectResponse
    {
        $member = User::findOrFail($request->validated('user_id'));

        $action->execute($organization, $member);

        return redirect()->route('organizations.edit', $organization)->with('status', 'member-added');
    }

    public function removeMember(RemoveOrganizationMemberRequest $request, Organization $organization, User $user, RemoveOrganizationMemberAction $action): RedirectResponse
    {
        $action->execute($organization, $user->id);

        return redirect()->route('organizations.edit', $organization)->with('status', 'member-removed');
    }
}
