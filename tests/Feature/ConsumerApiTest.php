<?php

namespace Tests\Feature;

use App\Models\Buku;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConsumerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_openlibrary_consumer_success(): void
    {
        $buku = Buku::first();
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $buku->isbn);
        $bibKey = "ISBN:{$cleanIsbn}";

        Http::fake([
            'https://openlibrary.org/api/books*' => Http::response([
                $bibKey => [
                    'title' => 'Mocked Book Title from OpenLibrary',
                    'authors' => [['name' => 'Mock Author']],
                    'publishers' => [['name' => 'Mock Publisher']],
                    'publish_date' => '2023',
                    'number_of_pages' => 450,
                    'url' => 'https://openlibrary.org/mock',
                    'cover' => ['medium' => 'https://covers.openlibrary.org/mock.jpg'],
                ],
            ], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$buku->id}/referensi-eksternal");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'external_source' => 'OpenLibrary REST API',
                'ditemukan' => true,
                'metadata' => [
                    'title' => 'Mocked Book Title from OpenLibrary',
                    'number_of_pages' => 450,
                ],
            ],
        ]);
    }

    public function test_openlibrary_consumer_fallback_when_not_found(): void
    {
        $buku = Buku::first();

        Http::fake([
            'https://openlibrary.org/api/books*' => Http::response([], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$buku->id}/referensi-eksternal");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'ditemukan' => false,
                'metadata' => null,
            ],
        ]);
    }

    public function test_github_consumer_success(): void
    {
        Http::fake([
            'https://api.github.com/users/octocat' => Http::response([
                'login' => 'octocat',
                'name' => 'The Octocat',
                'bio' => 'GitHub Mascot',
                'public_repos' => 8,
                'followers' => 1000,
                'html_url' => 'https://github.com/octocat',
            ], 200),
            'https://api.github.com/users/octocat/repos*' => Http::response([
                [
                    'name' => 'Spoon-Knife',
                    'language' => 'HTML',
                    'stargazers_count' => 12000,
                    'html_url' => 'https://github.com/octocat/Spoon-Knife',
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/v1/portfolios/github/octocat');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'github_login' => 'octocat',
                'display_name' => 'The Octocat',
                'latest_repos' => [
                    [
                        'repo_name' => 'Spoon-Knife',
                    ],
                ],
            ],
        ]);
    }
}
