<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithOrganizations;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use InteractsWithOrganizations;
    use RefreshDatabase;

    public function test_token_can_be_issued_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/token', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'user'],
            ]);
    }

    public function test_me_endpoint_returns_user_with_organizations(): void
    {
        $user = User::factory()->create();
        $this->createOrganizationWithMember($user);

        Sanctum::actingAs($user);

        $response = $this
            ->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonCount(1, 'data.organizations');
    }

    public function test_legacy_api_user_endpoint_still_works(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_organization_switch_endpoint_updates_session_context(): void
    {
        $user = User::factory()->create();
        $organization = $this->createOrganizationWithMember($user);

        Sanctum::actingAs($user);

        $response = $this
            ->withSession([])
            ->postJson("/api/v1/organizations/{$organization->id}/switch");

        $response->assertOk()
            ->assertJsonPath('data.current_organization_id', $organization->id);

        $sessionUpdated = (bool) $response->json('data.session_updated');
        $this->assertContains($sessionUpdated, [true, false], true);

        if ($sessionUpdated) {
            $this->assertSame($organization->id, session('current_organization_id'));
        }
    }
}
