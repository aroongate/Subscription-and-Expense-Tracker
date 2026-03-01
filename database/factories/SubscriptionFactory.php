<?php

namespace Database\Factories;

use App\Enums\SubscriptionBillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $amountMinor = fake()->numberBetween(500, 50000);

        return [
            'organization_id' => Organization::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->company().' Plan',
            'vendor' => fake()->company(),
            'amount_minor' => $amountMinor,
            'currency_code' => 'RUB',
            'exchange_rate' => 1,
            'amount_base_minor' => $amountMinor,
            'billing_cycle' => fake()->randomElement(SubscriptionBillingCycle::values()),
            'next_charge_at' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'status' => SubscriptionStatus::Active->value,
            'notes' => fake()->sentence(),
        ];
    }
}
