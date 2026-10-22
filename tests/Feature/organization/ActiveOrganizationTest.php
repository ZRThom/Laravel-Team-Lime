<?php

namespace Tests\Feature\organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private function userWithOrganizations(int $count = 2): array
    {
        $user = User::factory()->create();
        $organizations = Organization::factory()->count($count)->create();
        foreach ($organizations as $organization) {
            $organization->users()->attach($user->id, ['role' => 'member']);
        }

        return [$user, $organizations];
    }

    public function test_the_first_organization_is_active_by_default(): void
    {
        [$user, $organizations] = $this->userWithOrganizations();

        $this->actingAs($user)->get('/dashboard')->assertSessionHas('organization_id', $organizations[0]->id);
    }

    public function test_a_member_can_switch_the_active_organization(): void
    {
        [$user, $organizations] = $this->userWithOrganizations();

        $this->actingAs($user)->post("/organizations/{$organizations[1]->id}/switch")->assertRedirect();

        $this->assertSame($organizations[1]->id, session('organization_id'));
        $this->actingAs($user)->get('/dashboard')->assertSessionHas('organization_id', $organizations[1]->id);
    }

    public function test_a_user_cannot_switch_to_an_organization_he_is_not_member_of(): void
    {
        [$user] = $this->userWithOrganizations(1);
        $foreign = Organization::factory()->create();

        $this->actingAs($user)->post("/organizations/{$foreign->id}/switch")->assertForbidden();
    }

    public function test_the_choice_falls_back_to_a_valid_organization_when_membership_is_lost(): void
    {
        [$user, $organizations] = $this->userWithOrganizations();
        $this->actingAs($user)->withSession(['organization_id' => $organizations[1]->id]);
        $organizations[1]->users()->detach($user->id);

        $this->get('/dashboard')->assertSessionHas('organization_id', $organizations[0]->id);
    }

    public function test_the_menu_lists_the_organizations_of_the_user(): void
    {
        [$user, $organizations] = $this->userWithOrganizations();

        $this->actingAs($user)->get('/dashboard')->assertSee($organizations[0]->name)->assertSee($organizations[1]->name);
    }

    public function test_a_user_without_organization_has_no_active_one(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk();

        $this->assertNull(session('organization_id'));
    }
}
