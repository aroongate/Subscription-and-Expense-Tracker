<?php

namespace Tests\Feature\Api;

use App\Enums\OrganizationRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithOrganizations;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use InteractsWithOrganizations;
    use RefreshDatabase;

    public function test_owner_can_create_category(): void
    {
        $owner = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner);

        Sanctum::actingAs($owner);

        $response = $this
            ->withHeaders($this->orgHeader($organization))
            ->postJson('/api/v1/categories', [
                'type' => 'expense',
                'name' => 'Groceries',
                'color' => '#22c55e',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Groceries');

        $this->assertDatabaseHas('categories', [
            'organization_id' => $organization->id,
            'name' => 'Groceries',
            'type' => 'expense',
        ]);
    }

    public function test_member_cannot_create_category(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner, $member, OrganizationRole::Member->value);

        Sanctum::actingAs($member);

        $response = $this
            ->withHeaders($this->orgHeader($organization))
            ->postJson('/api/v1/categories', [
                'type' => 'expense',
                'name' => 'Blocked',
                'color' => '#22c55e',
            ]);

        $response->assertStatus(403);
    }

    public function test_member_can_view_categories(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner, $member, OrganizationRole::Member->value);

        Category::factory()->create([
            'organization_id' => $organization->id,
            'type' => 'expense',
            'name' => 'Transport',
        ]);

        Sanctum::actingAs($member);

        $response = $this
            ->withHeaders($this->orgHeader($organization))
            ->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Transport');
    }

    public function test_category_update_is_blocked_for_other_organization_context(): void
    {
        $owner = User::factory()->create();
        $organizationA = $this->createOrganizationWithMember($owner);
        $organizationB = $this->createOrganizationWithMember($owner);

        $category = Category::factory()->create([
            'organization_id' => $organizationA->id,
            'type' => 'expense',
        ]);

        Sanctum::actingAs($owner);

        $response = $this
            ->withHeaders($this->orgHeader($organizationB))
            ->patchJson("/api/v1/categories/{$category->id}", [
                'name' => 'Updated',
            ]);

        $response->assertStatus(404);
    }

    public function test_category_validation_rejects_invalid_type(): void
    {
        $owner = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner);

        Sanctum::actingAs($owner);

        $response = $this
            ->withHeaders($this->orgHeader($organization))
            ->postJson('/api/v1/categories', [
                'type' => 'invalid',
                'name' => 'Bad',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }
}
