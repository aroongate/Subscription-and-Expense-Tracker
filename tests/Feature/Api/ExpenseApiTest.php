<?php

namespace Tests\Feature\Api;

use App\Enums\CategoryType;
use App\Enums\OrganizationRole;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithOrganizations;
use Tests\TestCase;

class ExpenseApiTest extends TestCase
{
    use InteractsWithOrganizations;
    use RefreshDatabase;

    public function test_member_can_crud_expenses(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner, $member, OrganizationRole::Member->value);

        $category = Category::factory()->create([
            'organization_id' => $organization->id,
            'type' => CategoryType::Expense->value,
        ]);

        Sanctum::actingAs($member);

        $createResponse = $this
            ->withHeaders($this->orgHeader($organization))
            ->postJson('/api/v1/expenses', [
                'title' => 'Taxi',
                'amount_minor' => 15000,
                'currency_code' => 'RUB',
                'exchange_rate' => 1,
                'spent_at' => now()->toDateString(),
                'category_id' => $category->id,
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.title', 'Taxi');

        $expenseId = $createResponse->json('data.id');

        Sanctum::actingAs($member);

        $updateResponse = $this
            ->withHeaders($this->orgHeader($organization))
            ->patchJson("/api/v1/expenses/{$expenseId}", [
                'title' => 'Taxi (updated)',
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.title', 'Taxi (updated)');

        Sanctum::actingAs($member);

        $deleteResponse = $this
            ->withHeaders($this->orgHeader($organization))
            ->deleteJson("/api/v1/expenses/{$expenseId}");

        $deleteResponse->assertOk();

        $this->assertDatabaseMissing('expenses', ['id' => $expenseId]);
    }

    public function test_expense_validation_rejects_negative_amount(): void
    {
        $owner = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner);

        Sanctum::actingAs($owner);

        $response = $this
            ->withHeaders($this->orgHeader($organization))
            ->postJson('/api/v1/expenses', [
                'title' => 'Invalid',
                'amount_minor' => -100,
                'currency_code' => 'RUB',
                'spent_at' => now()->toDateString(),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount_minor']);
    }

    public function test_expense_tenant_isolation(): void
    {
        $owner = User::factory()->create();
        $organizationA = $this->createOrganizationWithMember($owner);
        $organizationB = $this->createOrganizationWithMember($owner);

        Expense::factory()->create([
            'organization_id' => $organizationA->id,
            'created_by_user_id' => $owner->id,
        ]);

        Expense::factory()->create([
            'organization_id' => $organizationB->id,
            'created_by_user_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this
            ->withHeaders($this->orgHeader($organizationA))
            ->getJson('/api/v1/expenses');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
}
