<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_successful_returns_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'token_type' => 'Bearer',
                'user' => [
                    'email' => 'pustakawan@del.ac.id',
                ],
            ],
        ]);
        $this->assertNotEmpty($response->json('data.access_token'));
    }

    public function test_login_invalid_credentials_returns_401_problem_details(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Invalid Credentials',
            'status' => 401,
        ]);
    }

    public function test_authenticated_me_and_logout_flow(): void
    {
        $user = User::where('email', 'pustakawan@del.ac.id')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        // Test GET /api/v1/auth/me
        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200);
        $meResponse->assertJson([
            'success' => true,
            'data' => [
                'email' => 'pustakawan@del.ac.id',
            ],
        ]);

        // Test POST /api/v1/auth/logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200);
        $logoutResponse->assertJson([
            'success' => true,
            'message' => 'Bearer Token berhasil dicabut (revoked). Sesi API diakhiri.',
        ]);

        // Flush guard cache so Sanctum re-authenticates token from DB
        app('auth')->forgetGuards();

        // Subsequent request with the same revoked token must be rejected with 401
        $meAfterLogout = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meAfterLogout->assertStatus(401);
    }
}
