<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionStatus;
use App\Models\Expense;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithOrganizations;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use InteractsWithOrganizations;
    use RefreshDatabase;

    public function test_dashboard_summary_and_series_are_returned(): void
    {
        $owner = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner);

        Expense::factory()->create([
            'organization_id' => $organization->id,
            'created_by_user_id' => $owner->id,
            'amount_base_minor' => 5000,
            'amount_minor' => 5000,
            'spent_at' => now()->subMonth()->toDateString(),
        ]);

        Subscription::factory()->create([
            'organization_id' => $organization->id,
            'amount_base_minor' => 3000,
            'amount_minor' => 3000,
            'status' => SubscriptionStatus::Active->value,
            'next_charge_at' => now()->toDateString(),
        ]);

        Sanctum::actingAs($owner);

        $summaryResponse = $this
            ->withHeaders($this->orgHeader($organization))
            ->getJson('/api/v1/dashboard/summary');

        $summaryResponse->assertOk()
            ->assertJsonPath('data.totals.expenses_base_minor', 5000)
            ->assertJsonPath('data.totals.subscriptions_base_minor', 3000);

        Sanctum::actingAs($owner);

        $seriesResponse = $this
            ->withHeaders($this->orgHeader($organization))
            ->getJson('/api/v1/dashboard/series?from='.now()->subMonths(1)->toDateString().'&to='.now()->toDateString());

        $seriesResponse->assertOk();
        $this->assertNotEmpty($seriesResponse->json('data.series'));
    }
}
