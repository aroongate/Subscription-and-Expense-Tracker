<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Concerns\InteractsWithOrganizations;
use Tests\TestCase;

class InertiaPagesTest extends TestCase
{
    use InteractsWithOrganizations;
    use RefreshDatabase;

    public function test_dashboard_page_loads_with_inertia_component(): void
    {
        $user = User::factory()->create();
        $this->createOrganizationWithMember($user);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard/Index')
                ->has('auth.user')
                ->has('organizations')
            );
    }

    public function test_expenses_page_loads(): void
    {
        $user = User::factory()->create();
        $this->createOrganizationWithMember($user);

        $this->actingAs($user)
            ->get('/expenses')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Expenses/Index'));
    }

    public function test_subscriptions_page_loads(): void
    {
        $user = User::factory()->create();
        $this->createOrganizationWithMember($user);

        $this->actingAs($user)
            ->get('/subscriptions')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Subscriptions/Index'));
    }

    public function test_categories_page_loads(): void
    {
        $user = User::factory()->create();
        $this->createOrganizationWithMember($user);

        $this->actingAs($user)
            ->get('/categories')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Categories/Index'));
    }

    public function test_organization_settings_page_loads(): void
    {
        $user = User::factory()->create();
        $this->createOrganizationWithMember($user);

        $this->actingAs($user)
            ->get('/settings/organization')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Settings/Organization'));
    }
}
