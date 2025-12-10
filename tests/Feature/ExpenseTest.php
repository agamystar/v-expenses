<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    //use RefreshDatabase;

    private function getAdminToken(): string
    {
        $admin = User::factory()->create(['role' => 'admin']);
        return $admin->createToken('test-token')->plainTextToken;
    }

    private function getStaffToken(User $user = null): string
    {
        $staff = $user ?? User::factory()->create(['role' => 'staff']);
        return $staff->createToken('test-token')->plainTextToken;
    }

    public function test_staff_can_create_expense(): void
    {
        $category = ExpenseCategory::factory()->create(['is_active' => true]);
        $vendor = Vendor::factory()->create(['is_active' => true]);
        $token = $this->getStaffToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/expenses', [
                'category_id' => $category->id,
                'vendor_id' => $vendor->id,
                'amount' => 100.50,
                'date' => '2024-01-15',
                'description' => 'Test expense',
            ]);

        $response->assertStatus(201)
            ->assertJson(['data' => ['amount' => '100.50']]);

        $this->assertDatabaseHas('expenses', [
            'amount' => 100.50,
            'date' => '2024-01-15',
        ]);
    }

    public function test_expense_creation_requires_active_category(): void
    {
        $category = ExpenseCategory::factory()->create(['is_active' => false]);
        $token = $this->getStaffToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/expenses', [
                'category_id' => $category->id,
                'amount' => 100.50,
                'date' => '2024-01-15',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_expense_amount_must_be_greater_than_zero(): void
    {
        $category = ExpenseCategory::factory()->create(['is_active' => true]);
        $token = $this->getStaffToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/expenses', [
                'category_id' => $category->id,
                'amount' => 0,
                'date' => '2024-01-15',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_staff_can_only_delete_own_expenses(): void
    {
        $staff1 = User::factory()->create(['role' => 'staff']);
        $staff2 = User::factory()->create(['role' => 'staff']);
        
        $expense = Expense::factory()->create(['created_by' => $staff1->id]);
        $token = $this->getStaffToken($staff2);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/v1/expenses/{$expense->id}");

        $response->assertStatus(403);
    }

    public function test_staff_can_delete_own_expense(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $expense = Expense::factory()->create(['created_by' => $staff->id]);
        $token = $this->getStaffToken($staff);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/v1/expenses/{$expense->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_admin_can_delete_any_expense(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $expense = Expense::factory()->create(['created_by' => $staff->id]);
        $token = $this->getAdminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/v1/expenses/{$expense->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_expenses_can_be_filtered_by_date(): void
    {
        $testDate = '2024-01-15';
        $otherDate = '2024-02-20';
        
        $testExpense = Expense::factory()->create(['date' => $testDate, 'description' => 'Test Expense ' . uniqid()]);
        Expense::factory()->create(['date' => $otherDate, 'description' => 'Other Expense ' . uniqid()]);
        $token = $this->getAdminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/expenses?date={$testDate}");

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $expenseIds = collect($data)->pluck('id')->toArray();
        
        // Check that our test expense is in the filtered results
        $this->assertContains($testExpense->id, $expenseIds);
        
        // Check that all returned expenses have the correct date
        foreach ($data as $expense) {
            $this->assertEquals($testDate, $expense['date']);
        }
    }

    public function test_staff_can_only_see_own_expenses(): void
    {
        $staff1 = User::factory()->create(['role' => 'staff']);
        $staff2 = User::factory()->create(['role' => 'staff']);
        
        Expense::factory()->create(['created_by' => $staff1->id]);
        Expense::factory()->create(['created_by' => $staff2->id]);
        
        $token = $this->getStaffToken($staff1);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/expenses');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
