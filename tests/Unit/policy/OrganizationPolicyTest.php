<?php

namespace Tests\Unit\policy;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrganizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    // role => [viewAny, view, create, update, delete]
    public static function roles(): array
    {
        return [
            'admin' => ['admin', true, true, true, true, true],
            'owner' => ['owner', true, true, true, true, true],
            'member' => ['member', true, true, true, false, false],
            'owner of another organization' => ['other_owner', true, false, true, false, false],
            'connected user without organization' => ['stranger', true, false, true, false, false],
        ];
    }

    private function userWithRole(string $role, Organization $organization): User
    {
        $user = $role === 'admin' ? User::factory()->admin()->create() : User::factory()->create();

        if (in_array($role, ['owner', 'member'], true)) {
            $organization->users()->attach($user->id, ['role' => $role]);
        }

        if ($role === 'other_owner') {
            Organization::factory()->create()->users()->attach($user->id, ['role' => 'owner']);
        }

        return $user;
    }

    #[DataProvider('roles')]
    public function test_organization_abilities_depend_on_the_role(string $role, bool $viewAny, bool $view, bool $create, bool $update, bool $delete): void
    {
        $organization = Organization::factory()->create();
        $gate = Gate::forUser($this->userWithRole($role, $organization));

        $this->assertSame($viewAny, $gate->allows('viewAny', Organization::class));
        $this->assertSame($view, $gate->allows('view', $organization));
        $this->assertSame($create, $gate->allows('create', Organization::class));
        $this->assertSame($update, $gate->allows('update', $organization));
        $this->assertSame($delete, $gate->allows('delete', $organization));
    }

    public function test_guests_cannot_do_anything(): void
    {
        $organization = Organization::factory()->create();

        $this->assertFalse(Gate::allows('viewAny', Organization::class));
        $this->assertFalse(Gate::allows('view', $organization));
        $this->assertFalse(Gate::allows('create', Organization::class));
        $this->assertFalse(Gate::allows('update', $organization));
        $this->assertFalse(Gate::allows('delete', $organization));
    }

    public function test_role_in_returns_the_role_of_the_user_in_the_organization(): void
    {
        $organization = Organization::factory()->create();
        $owner = $this->userWithRole('owner', $organization);
        $member = $this->userWithRole('member', $organization);
        $stranger = $this->userWithRole('stranger', $organization);

        $this->assertSame('owner', $owner->roleIn($organization));
        $this->assertSame('member', $member->roleIn($organization));
        $this->assertNull($stranger->roleIn($organization));
    }
}
