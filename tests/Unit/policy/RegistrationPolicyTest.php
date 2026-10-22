<?php

namespace Tests\Unit\policy;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RegistrationPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function ownerOf(Event $event): User
    {
        $user = User::factory()->create();
        $event->organization->users()->attach($user->id, ['role' => 'owner']);

        return $user;
    }

    public function test_the_list_of_participants_is_for_the_owner_and_the_admin(): void
    {
        $event = Event::factory()->create();
        $member = User::factory()->create();
        $event->organization->users()->attach($member->id, ['role' => 'member']);

        $this->assertTrue(Gate::forUser($this->ownerOf($event))->allows('registration.viewAny', $event));
        $this->assertTrue(Gate::forUser(User::factory()->admin()->create())->allows('registration.viewAny', $event));
        $this->assertFalse(Gate::forUser($member)->allows('registration.viewAny', $event));
        $this->assertFalse(Gate::forUser(User::factory()->create())->allows('registration.viewAny', $event));
    }

    public function test_the_owner_of_another_organization_cannot_list_the_participants(): void
    {
        $event = Event::factory()->create();
        $otherOwner = $this->ownerOf(Event::factory()->create());

        $this->assertFalse(Gate::forUser($otherOwner)->allows('registration.viewAny', $event));
    }

    public function test_any_connected_user_can_participate_but_not_a_visitor(): void
    {
        $event = Event::factory()->create();

        $this->assertTrue(Gate::forUser(User::factory()->create())->allows('registration.create', $event));
        $this->assertFalse(Gate::allows('registration.create', $event));
    }

    public function test_a_participant_can_see_and_cancel_only_his_own_registration(): void
    {
        $event = Event::factory()->create();
        [$participant, $other] = User::factory()->count(2)->create()->all();
        $event->participants()->attach($participant->id, ['status' => 'confirmed']);

        $this->assertTrue(Gate::forUser($participant)->allows('registration.view', $event));
        $this->assertTrue(Gate::forUser($participant)->allows('registration.cancel', $event));
        $this->assertFalse(Gate::forUser($other)->allows('registration.view', $event));
        $this->assertFalse(Gate::forUser($other)->allows('registration.cancel', $event));
    }

    public function test_owner_and_admin_cannot_cancel_the_registration_of_a_participant(): void
    {
        $event = Event::factory()->create();
        $event->participants()->attach(User::factory()->create()->id, ['status' => 'confirmed']);

        $this->assertFalse(Gate::forUser($this->ownerOf($event))->allows('registration.cancel', $event));
        $this->assertFalse(Gate::forUser(User::factory()->admin()->create())->allows('registration.cancel', $event));
    }

    public function test_a_visitor_can_do_nothing(): void
    {
        $event = Event::factory()->create();

        foreach (['viewAny', 'view', 'create', 'cancel'] as $ability) {
            $this->assertFalse(Gate::allows('registration.'.$ability, $event));
        }
    }
}
