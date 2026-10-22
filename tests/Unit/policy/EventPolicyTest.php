<?php

namespace Tests\Unit\policy;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

// update and cancel are already covered by tests/Feature/event/EventAuthorizationTest
class EventPolicyTest extends TestCase
{
    use RefreshDatabase;

    // role => [create, view of a draft event]
    public static function roles(): array
    {
        return [
            'admin' => ['admin', true, true],
            'owner' => ['owner', true, true],
            'member' => ['member', false, true],
            'connected user without organization' => ['stranger', false, false],
        ];
    }

    private function userWithRole(string $role, Organization $organization): User
    {
        $user = $role === 'admin' ? User::factory()->admin()->create() : User::factory()->create();

        if (in_array($role, ['owner', 'member'], true)) {
            $organization->users()->attach($user->id, ['role' => $role]);
        }

        return $user;
    }

    #[DataProvider('roles')]
    public function test_create_and_view_of_a_draft_depend_on_the_role(string $role, bool $create, bool $viewDraft): void
    {
        $event = Event::factory()->draft()->create();
        $gate = Gate::forUser($this->userWithRole($role, $event->organization));

        $this->assertTrue($gate->allows('viewAny', Event::class));
        $this->assertSame($create, $gate->allows('create', Event::class));
        $this->assertSame($viewDraft, $gate->allows('view', $event));
    }

    public function test_a_published_event_can_be_seen_by_everybody_visitors_included(): void
    {
        $event = Event::factory()->create();

        $this->assertTrue(Gate::allows('viewAny', Event::class));
        $this->assertTrue(Gate::allows('view', $event));
        $this->assertTrue(Gate::forUser(User::factory()->create())->allows('view', $event));
    }

    public function test_a_draft_or_cancelled_event_is_hidden_from_visitors(): void
    {
        $this->assertFalse(Gate::allows('view', Event::factory()->draft()->create()));
        $this->assertFalse(Gate::allows('view', Event::factory()->cancelled()->create()));
    }

    public function test_a_visitor_cannot_create_an_event(): void
    {
        $this->assertFalse(Gate::allows('create', Event::class));
    }

    public function test_a_member_cannot_update_or_cancel_an_event(): void
    {
        $event = Event::factory()->create();
        $member = $this->userWithRole('member', $event->organization);

        $this->assertFalse(Gate::forUser($member)->allows('update', $event));
        $this->assertFalse(Gate::forUser($member)->allows('cancel', $event));
    }
}
