<?php

namespace Tests\Feature\Api;

use App\Enums\CategoryType;
use App\Enums\OrganizationRole;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithOrganizations;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use InteractsWithOrganizations;
    use RefreshDatabase;

    public function test_member_can_crud_subscriptions(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner, $member, OrganizationRole::Member->value);

        $category = Category::factory()->create([
            'organization_id' => $organization->id,
            'type' => CategoryType::Subscription->value,
        ]);

        Sanctum::actingAs($member);

        $createResponse = $this
            ->withHeaders($this->orgHeader($organization))
            ->postJson('/api/v1/subscriptions', [
                'name' => 'Netflix',
                'vendor' => 'Netflix',
                'amount_minor' => 99900,
                'currency_code' => 'RUB',
                'exchange_rate' => 1,
                'billing_cycle' => 'monthly',
                'next_charge_at' => now()->addDays(7)->toDateString(),
                'status' => 'active',
                'category_id' => $category->id,
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.name', 'Netflix');

        $subscriptionId = $createResponse->json('data.id');

        Sanctum::actingAs($member);

        $updateResponse = $this
            ->withHeaders($this->orgHeader($organization))
            ->patchJson("/api/v1/subscriptions/{$subscriptionId}", [
                'status' => 'paused',
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.status', 'paused');

        Sanctum::actingAs($member);

        $deleteResponse = $this
            ->withHeaders($this->orgHeader($organization))
            ->deleteJson("/api/v1/subscriptions/{$subscriptionId}");

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('subscriptions', ['id' => $subscriptionId]);
    }

    public function test_subscription_validation_rejects_invalid_status(): void
    {
        $owner = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner);

        Sanctum::actingAs($owner);

        $response = $this
            ->withHeaders($this->orgHeader($organization))
            ->postJson('/api/v1/subscriptions', [
                'name' => 'Invalid',
                'amount_minor' => 1000,
                'currency_code' => 'RUB',
                'billing_cycle' => 'monthly',
                'next_charge_at' => now()->toDateString(),
                'status' => 'wrong',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_subscription_tenant_isolation(): void
    {
        $owner = User::factory()->create();
        $organizationA = $this->createOrganizationWithMember($owner);
        $organizationB = $this->createOrganizationWithMember($owner);

        Subscription::factory()->create([
            'organization_id' => $organizationA->id,
        ]);

        Subscription::factory()->create([
            'organization_id' => $organizationB->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this
            ->withHeaders($this->orgHeader($organizationA))
            ->getJson('/api/v1/subscriptions');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
}
