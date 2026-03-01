<?php

namespace Tests\Feature\Api;

use App\Enums\OrganizationRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithOrganizations;
use Tests\TestCase;

class OrganizationMemberPolicyTest extends TestCase
{
    use InteractsWithOrganizations;
    use RefreshDatabase;

    public function test_member_cannot_manage_organization_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner, $member, OrganizationRole::Member->value);

        $target = User::factory()->create();

        Sanctum::actingAs($member);

        $response = $this
            ->withHeaders($this->orgHeader($organization))
            ->postJson("/api/v1/organizations/{$organization->id}/members", [
                'email' => $target->email,
                'role' => OrganizationRole::Member->value,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_assign_owner_role(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $target = User::factory()->create();

        $organization = $this->createOrganizationWithMember($owner, $admin, OrganizationRole::Admin->value);
        $organization->users()->attach($target->id, [
            'role' => OrganizationRole::Member->value,
        ]);

        Sanctum::actingAs($admin);

        $response = $this
            ->withHeaders($this->orgHeader($organization))
            ->patchJson("/api/v1/organizations/{$organization->id}/members/{$target->id}", [
                'role' => OrganizationRole::Owner->value,
            ]);

        $response->assertStatus(403);
    }
}
