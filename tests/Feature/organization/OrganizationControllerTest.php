<?php

namespace Tests\Feature\organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): array
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->users()->attach($user->id, ['role' => $role]);

        return [$user, $organization];
    }

    private function payload(array $changes = []): array
    {
        return array_merge(['name' => 'Collectif Lime', 'slug' => 'collectif-lime', 'plan' => 'free'], $changes);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/organizations')->assertRedirect('/login');
        $this->get('/organizations/create')->assertRedirect('/login');
    }

    public function test_the_list_shows_only_my_organizations(): void
    {
        [$user, $mine] = $this->userWithRole('member');
        $foreign = Organization::factory()->create();

        $this->actingAs($user)->get('/organizations')->assertOk()->assertSee($mine->name)->assertDontSee($foreign->name);
    }

    public function test_an_admin_sees_every_organization(): void
    {
        $first = Organization::factory()->create();
        $second = Organization::factory()->create();

        $this->actingAs(User::factory()->admin()->create())->get('/organizations')->assertOk()
            ->assertSee($first->name)->assertSee($second->name);
    }

    public function test_a_member_sees_the_organization_but_has_no_edit_link(): void
    {
        [$member, $organization] = $this->userWithRole('member');

        $this->actingAs($member)->get('/organizations')->assertOk()
            ->assertSee($organization->name)
            ->assertDontSee(route('organizations.edit', $organization));
    }

    public function test_an_owner_sees_the_edit_link(): void
    {
        [$owner, $organization] = $this->userWithRole('owner');

        $this->actingAs($owner)->get('/organizations')->assertSee(route('organizations.edit', $organization));
    }

    public function test_any_connected_user_can_create_an_organization_and_becomes_its_owner(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/organizations/create')->assertOk();
        $this->actingAs($user)->post('/organizations', $this->payload())->assertRedirect(route('organizations.index'));

        $organization = Organization::firstOrFail();
        $this->assertSame('owner', $user->roleIn($organization));
    }

    public function test_a_member_cannot_edit_update_delete_or_manage_members(): void
    {
        [$member, $organization] = $this->userWithRole('member');
        $other = User::factory()->create();

        $this->actingAs($member)->get("/organizations/{$organization->id}/edit")->assertForbidden();
        $this->actingAs($member)->put("/organizations/{$organization->id}", $this->payload())->assertForbidden();
        $this->actingAs($member)->delete("/organizations/{$organization->id}")->assertForbidden();
        $this->actingAs($member)->post("/organizations/{$organization->id}/members", ['user_id' => $other->id])->assertForbidden();
        $this->actingAs($member)->delete("/organizations/{$organization->id}/members/{$member->id}")->assertForbidden();

        $this->assertDatabaseHas('organizations', ['id' => $organization->id, 'name' => $organization->name]);
        $this->assertSame('member', $member->roleIn($organization));
    }

    public function test_a_user_outside_the_organization_gets_a_403(): void
    {
        $organization = Organization::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get("/organizations/{$organization->id}/edit")->assertForbidden();
        $this->actingAs($stranger)->delete("/organizations/{$organization->id}")->assertForbidden();
        $this->assertDatabaseHas('organizations', ['id' => $organization->id]);
    }

    public function test_an_owner_acts_on_his_organization_but_not_on_another_one(): void
    {
        [$owner, $mine] = $this->userWithRole('owner');
        $foreign = Organization::factory()->create();

        $this->actingAs($owner)->get("/organizations/{$mine->id}/edit")->assertOk();
        $this->actingAs($owner)->put("/organizations/{$mine->id}", $this->payload(['name' => 'Renamed']))->assertRedirect();
        $this->assertSame('Renamed', $mine->fresh()->name);

        $this->actingAs($owner)->get("/organizations/{$foreign->id}/edit")->assertForbidden();
        $this->actingAs($owner)->delete("/organizations/{$foreign->id}")->assertForbidden();
        $this->assertDatabaseHas('organizations', ['id' => $foreign->id]);
    }

    public function test_an_owner_can_add_and_remove_a_member_and_delete_the_organization(): void
    {
        [$owner, $organization] = $this->userWithRole('owner');
        $newcomer = User::factory()->create();

        $this->actingAs($owner)->post("/organizations/{$organization->id}/members", ['user_id' => $newcomer->id])->assertRedirect();
        $this->assertSame('member', $newcomer->roleIn($organization));

        $this->actingAs($owner)->delete("/organizations/{$organization->id}/members/{$newcomer->id}")->assertRedirect();
        $this->assertNull($newcomer->roleIn($organization));

        $this->actingAs($owner)->delete("/organizations/{$organization->id}")->assertRedirect(route('organizations.index'));
        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
    }

    public function test_an_admin_can_edit_and_delete_any_organization(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get("/organizations/{$organization->id}/edit")->assertOk();
        $this->actingAs($admin)->delete("/organizations/{$organization->id}")->assertRedirect();
        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
    }
}
