<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTest extends TestCase
{
    //use RefreshDatabase;

    private function getAdminToken(): string
    {
        $admin = User::factory()->create(['role' => 'admin']);
        return $admin->createToken('test-token')->plainTextToken;
    }

    private function getStaffToken(): string
    {
        $staff = User::factory()->create(['role' => 'staff']);
        return $staff->createToken('test-token')->plainTextToken;
    }

    public function test_admin_can_create_vendor(): void
    {
        $token = $this->getAdminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/vendors', [
                'name' => 'Test Vendor',
                'contact_info' => 'contact@vendor.com',
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson(['data' => ['name' => 'Test Vendor']]);

        $this->assertDatabaseHas('vendors', ['name' => 'Test Vendor']);
    }

    public function test_staff_cannot_create_vendor(): void
    {
        $token = $this->getStaffToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/vendors', [
                'name' => 'Test Vendor',
            ]);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_list_vendors(): void
    {
        $testVendors = Vendor::factory()->count(3)->create(['name' => 'Test Vendor ' . uniqid()]);
        $token = $this->getStaffToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/vendors');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $vendorIds = collect($data)->pluck('id')->toArray();
        
        // Check that our test vendors are in the response
        foreach ($testVendors as $vendor) {
            $this->assertContains($vendor->id, $vendorIds);
        }
    }

    public function test_admin_can_update_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        $token = $this->getAdminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/v1/vendors/{$vendor->id}", [
                'name' => 'Updated Vendor',
            ]);

        $response->assertStatus(200)
            ->assertJson(['data' => ['name' => 'Updated Vendor']]);

        $this->assertDatabaseHas('vendors', ['name' => 'Updated Vendor']);
    }

    public function test_admin_can_delete_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        $token = $this->getAdminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/v1/vendors/{$vendor->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);
    }
}
