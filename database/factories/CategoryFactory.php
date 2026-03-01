<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'type' => fake()->randomElement(CategoryType::values()),
            'name' => fake()->words(2, true),
            'color' => sprintf('#%06X', fake()->numberBetween(0, 0xFFFFFF)),
            'is_active' => true,
        ];
    }
}
