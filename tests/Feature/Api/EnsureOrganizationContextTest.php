<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithOrganizations;
use Tests\TestCase;

class EnsureOrganizationContextTest extends TestCase
{
    use InteractsWithOrganizations;
    use RefreshDatabase;

    public function test_it_returns_400_when_org_header_is_missing(): void
    {
        $user = User::factory()->create();
        $organization = $this->createOrganizationWithMember($user);

        Sanctum::actingAs($user);

        $response = $this
            ->getJson('/api/v1/categories');

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Organization ID is required');
    }

    public function test_it_returns_401_when_user_is_not_authenticated(): void
    {
        $organization = Organization::factory()->create();

        $response = $this->withHeaders($this->orgHeader($organization))
            ->getJson('/api/v1/categories');

        $response->assertStatus(401);
    }

    public function test_it_returns_403_when_user_is_not_organization_member(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner);

        Sanctum::actingAs($user);

        $response = $this
            ->withHeaders($this->orgHeader($organization))
            ->getJson('/api/v1/categories');

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You are not a member of this organization.');
    }

    public function test_it_returns_404_when_organization_does_not_exist(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this
            ->withHeaders(['X-Org-Id' => '999999'])
            ->getJson('/api/v1/categories');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Organization not found.');
    }
}
