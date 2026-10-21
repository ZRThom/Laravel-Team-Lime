<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public static function eventRoles(): array
    {
        return [
            'organization owner' => ['owner', true],
            'organization member' => ['member', false],
            'another organization owner' => ['other_owner', false],
            'creator without ownership' => ['creator', false],
            'administrator' => ['admin', true],
        ];
    }

    #[DataProvider('eventRoles')]
    public function test_event_management_depends_on_organization_ownership_or_admin_role(string $role, bool $allowed): void
    {
        $user = $role === 'admin' ? User::factory()->admin()->create() : User::factory()->create();
        $event = Event::factory()->create($role === 'creator' ? ['created_by' => $user->id] : []);

        if (in_array($role, ['owner', 'member'], true)) {
            $event->organization->users()->attach($user->id, ['role' => $role]);
        }

        if ($role === 'other_owner') {
            Organization::factory()->create()->users()->attach($user->id, ['role' => 'owner']);
        }

        $this->assertSame($role === 'owner', $event->isOwnedBy($user));
        $this->assertSame($allowed, Gate::forUser($user)->allows('update', $event));
        $this->assertSame($allowed, Gate::forUser($user)->allows('cancel', $event));
    }

    public function test_guests_cannot_manage_events(): void
    {
        $event = Event::factory()->create();

        $this->assertFalse(Gate::allows('update', $event));
        $this->assertFalse(Gate::allows('cancel', $event));
    }
}
