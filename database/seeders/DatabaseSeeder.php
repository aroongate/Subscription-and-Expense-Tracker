<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Enums\OrganizationRole;
use App\Enums\SubscriptionBillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $owner = User::factory()->create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $member = User::factory()->create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => Hash::make('password'),
        ]);

        $organization = Organization::query()->create([
            'name' => 'Acme Finance',
            'owner_user_id' => $owner->id,
            'base_currency_code' => 'RUB',
        ]);

        $organization->users()->attach($owner->id, ['role' => OrganizationRole::Owner->value]);
        $organization->users()->attach($admin->id, ['role' => OrganizationRole::Admin->value]);
        $organization->users()->attach($member->id, ['role' => OrganizationRole::Member->value]);

        $expenseCategories = collect([
            ['name' => 'Food', 'color' => '#f97316'],
            ['name' => 'Transport', 'color' => '#0ea5e9'],
            ['name' => 'Utilities', 'color' => '#14b8a6'],
        ])->map(fn (array $payload) => Category::query()->create([
            'organization_id' => $organization->id,
            'type' => CategoryType::Expense->value,
            'name' => $payload['name'],
            'color' => $payload['color'],
            'is_active' => true,
        ]));

        $subscriptionCategories = collect([
            ['name' => 'Tools', 'color' => '#8b5cf6'],
            ['name' => 'Streaming', 'color' => '#10b981'],
        ])->map(fn (array $payload) => Category::query()->create([
            'organization_id' => $organization->id,
            'type' => CategoryType::Subscription->value,
            'name' => $payload['name'],
            'color' => $payload['color'],
            'is_active' => true,
        ]));

        Subscription::query()->create([
            'organization_id' => $organization->id,
            'category_id' => $subscriptionCategories[0]->id,
            'name' => 'Notion Team',
            'vendor' => 'Notion',
            'amount_minor' => 129900,
            'currency_code' => 'RUB',
            'exchange_rate' => 1,
            'amount_base_minor' => 129900,
            'billing_cycle' => SubscriptionBillingCycle::Monthly->value,
            'next_charge_at' => now()->addDays(6)->toDateString(),
            'status' => SubscriptionStatus::Active->value,
            'notes' => 'Project management',
        ]);

        Subscription::query()->create([
            'organization_id' => $organization->id,
            'category_id' => $subscriptionCategories[1]->id,
            'name' => 'YouTube Premium',
            'vendor' => 'YouTube',
            'amount_minor' => 19900,
            'currency_code' => 'RUB',
            'exchange_rate' => 1,
            'amount_base_minor' => 19900,
            'billing_cycle' => SubscriptionBillingCycle::Monthly->value,
            'next_charge_at' => now()->addDays(12)->toDateString(),
            'status' => SubscriptionStatus::Active->value,
            'notes' => null,
        ]);

        foreach (range(1, 16) as $index) {
            $amountMinor = random_int(5000, 150000);

            Expense::query()->create([
                'organization_id' => $organization->id,
                'category_id' => $expenseCategories->random()->id,
                'created_by_user_id' => collect([$owner->id, $admin->id, $member->id])->random(),
                'title' => "Expense #{$index}",
                'amount_minor' => $amountMinor,
                'currency_code' => 'RUB',
                'exchange_rate' => 1,
                'amount_base_minor' => $amountMinor,
                'spent_at' => now()->subDays(random_int(0, 120))->toDateString(),
                'note' => null,
            ]);
        }
    }
}
