<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCategoryTest extends TestCase
{
    //use RefreshDatabase;

    private function getAdminToken(): string
    {
        $admin = User::factory()->create(['role' => 'admin']);
        return $admin->createToken('test-token')->plainTextToken;
    }

    public function test_admin_can_create_category(): void
    {
        $token = $this->getAdminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson(['data' => ['name' => 'Test Category']]);

        $this->assertDatabaseHas('expense_categories', ['name' => 'Test Category']);
    }

    public function test_cannot_delete_category_with_linked_expenses(): void
    {
        $category = ExpenseCategory::factory()->create();
        Expense::factory()->create(['category_id' => $category->id]);
        $token = $this->getAdminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(422)
            ->assertJson(['message' => 'Cannot delete category with linked expenses.']);

        $this->assertDatabaseHas('expense_categories', ['id' => $category->id]);
    }

    public function test_admin_can_delete_category_without_expenses(): void
    {
        $category = ExpenseCategory::factory()->create();
        $token = $this->getAdminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('expense_categories', ['id' => $category->id]);
    }

    public function test_authenticated_user_can_list_categories(): void
    {
        $testCategories = ExpenseCategory::factory()->count(3)->create(['name' => 'Test Category ' . uniqid()]);
        $staff = User::factory()->create(['role' => 'staff']);
        $token = $staff->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/categories');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $categoryIds = collect($data)->pluck('id')->toArray();
        
        // Check that our test categories are in the response
        foreach ($testCategories as $category) {
            $this->assertContains($category->id, $categoryIds);
        }
    }
}
