<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    //use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $uniqueEmail = 'test_' . uniqid() . '@example.com';
        
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => $uniqueEmail,
            'password' => 'password123',
            'role' => 'staff',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'role' => 'staff',
        ]);
    }

    public function test_user_can_login(): void
    {
        $uniqueEmail = 'login_' . uniqid() . '@example.com';
        $user = User::factory()->create([
            'email' => $uniqueEmail,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $uniqueEmail,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $uniqueEmail = 'invalid_' . uniqid() . '@example.com';
        // Create user first
        User::factory()->create([
            'email' => $uniqueEmail,
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $uniqueEmail,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }
}
