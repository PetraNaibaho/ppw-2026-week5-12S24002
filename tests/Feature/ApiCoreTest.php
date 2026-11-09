<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiCoreTest extends TestCase
{
    public function test_api_404_returns_rfc_7807_problem_details(): void
    {
        $response = $this->getJson('/api/v1/endpoint-tidak-ada');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Not Found',
            'status' => 404,
        ]);
        $this->assertArrayHasKey('type', $response->json());
        $this->assertArrayHasKey('detail', $response->json());
        $this->assertArrayHasKey('instance', $response->json());
    }

    public function test_api_unauthenticated_returns_rfc_7807_problem_details(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Unauthorized',
            'status' => 401,
        ]);
        $this->assertArrayHasKey('type', $response->json());
        $this->assertArrayHasKey('detail', $response->json());
        $this->assertArrayHasKey('instance', $response->json());
    }
}
