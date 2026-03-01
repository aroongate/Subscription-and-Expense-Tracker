<?php

namespace Tests\Unit\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Expense;
use App\Models\Subscription;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithOrganizations;
use Tests\TestCase;

class DashboardMetricsServiceTest extends TestCase
{
    use InteractsWithOrganizations;
    use RefreshDatabase;

    public function test_summary_calculates_totals(): void
    {
        $owner = User::factory()->create();
        $organization = $this->createOrganizationWithMember($owner);

        Expense::factory()->create([
            'organization_id' => $organization->id,
            'created_by_user_id' => $owner->id,
            'amount_base_minor' => 4000,
            'amount_minor' => 4000,
        ]);

        Subscription::factory()->create([
            'organization_id' => $organization->id,
            'amount_base_minor' => 6000,
            'amount_minor' => 6000,
            'status' => SubscriptionStatus::Active->value,
        ]);

        $service = app(DashboardMetricsService::class);

        $summary = $service->summary($organization);

        $this->assertSame(4000, $summary['totals']['expenses_base_minor']);
        $this->assertSame(6000, $summary['totals']['subscriptions_base_minor']);
        $this->assertSame(10000, $summary['totals']['combined_base_minor']);
    }
}
