<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    //use RefreshDatabase;

    private function getAdminToken(): string
    {
        $admin = User::factory()->create(['role' => 'admin']);
        return $admin->createToken('test-token')->plainTextToken;
    }

    public function test_can_get_summary_report(): void
    {
        $uniqueName = 'Test Category ' . uniqid();
        $category1 = ExpenseCategory::factory()->create(['name' => $uniqueName . ' 1']);
        $category2 = ExpenseCategory::factory()->create(['name' => $uniqueName . ' 2']);
        
        $testExpense1 = Expense::factory()->create([
            'category_id' => $category1->id,
            'amount' => 100,
            'date' => '2024-01-15',
            'description' => 'Test Expense 1 ' . uniqid(),
        ]);
        $testExpense2 = Expense::factory()->create([
            'category_id' => $category1->id,
            'amount' => 200,
            'date' => '2024-01-20',
            'description' => 'Test Expense 2 ' . uniqid(),
        ]);
        $testExpense3 = Expense::factory()->create([
            'category_id' => $category2->id,
            'amount' => 150,
            'date' => '2024-02-10',
            'description' => 'Test Expense 3 ' . uniqid(),
        ]);

        $token = $this->getAdminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/reports/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'summary' => [
                    '*' => ['month', 'category_id', 'category_name', 'total_amount', 'expense_count'],
                ],
                'total_expenses',
                'total_count',
            ]);

        $data = $response->json();
        $summary = collect($data['summary']);
        
        // Check that our test categories are in the summary
        $category1Summary = $summary->where('category_id', $category1->id)->first();
        $category2Summary = $summary->where('category_id', $category2->id)->first();
        
        $this->assertNotNull($category1Summary);
        $this->assertNotNull($category2Summary);
        
        // Check that category1 has 2 expenses totaling 300
        $this->assertEquals(2, $category1Summary['expense_count']);
        $this->assertEquals(300.0, $category1Summary['total_amount']);
        
        // Check that category2 has 1 expense totaling 150
        $this->assertEquals(1, $category2Summary['expense_count']);
        $this->assertEquals(150.0, $category2Summary['total_amount']);
    }
}
