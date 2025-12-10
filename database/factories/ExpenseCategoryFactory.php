<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseCategory>
 */
class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Office Supplies',
            'Travel',
            'Meals',
            'Software',
            'Hardware',
            'Marketing',
            'Utilities',
            'Rent',
            'Insurance',
            'Training',
        ];

        return [
            'name' => fake()->randomElement($categories),
            'is_active' => true,
        ];
    }
}
