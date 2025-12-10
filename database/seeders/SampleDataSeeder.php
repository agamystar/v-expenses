<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create vendors
        $vendors = \App\Models\Vendor::factory(10)->create();

        // Create categories
        $categories = \App\Models\ExpenseCategory::factory(8)->create();

        // Create expenses
        $users = \App\Models\User::all();
        \App\Models\Expense::factory(50)->create([
            'vendor_id' => fn() => $vendors->random()->id,
            'category_id' => fn() => $categories->random()->id,
            'created_by' => fn() => $users->random()->id,
        ]);
    }
}
