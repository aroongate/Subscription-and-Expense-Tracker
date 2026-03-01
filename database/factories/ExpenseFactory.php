<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $amountMinor = fake()->numberBetween(100, 200000);

        return [
            'organization_id' => Organization::factory(),
            'category_id' => Category::factory(),
            'created_by_user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'amount_minor' => $amountMinor,
            'currency_code' => 'RUB',
            'exchange_rate' => 1,
            'amount_base_minor' => $amountMinor,
            'spent_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'note' => fake()->sentence(),
        ];
    }
}
