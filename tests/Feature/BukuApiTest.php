<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BukuApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->user = User::where('email', 'pustakawan@del.ac.id')->first();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_get_bukus_index_with_pagination_and_hateoas(): void
    {
        $response = $this->getJson('/api/v1/bukus?per_page=5&sort=stok&dir=desc');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'isbn',
                    'judul',
                    'penulis',
                    'stok',
                    '_links' => ['self', 'kategori', 'referensi_eksternal'],
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total_items',
                'total_pages',
                'links',
            ],
        ]);
    }

    public function test_get_buku_detail_by_id_and_isbn(): void
    {
        $buku = Buku::first();

        // Cari via ID
        $resById = $this->getJson("/api/v1/bukus/{$buku->id}");
        $resById->assertStatus(200);
        $resById->assertJsonPath('data.isbn', $buku->isbn);
        $this->assertArrayHasKey('pinjam_buku', $resById->json('data._links'));

        // Cari via ISBN
        $resByIsbn = $this->getJson("/api/v1/bukus/{$buku->isbn}");
        $resByIsbn->assertStatus(200);
        $resByIsbn->assertJsonPath('data.id', $buku->id);
    }

    public function test_get_buku_not_found_returns_404_problem_details(): void
    {
        $response = $this->getJson('/api/v1/bukus/999999');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Resource Not Found',
            'status' => 404,
        ]);
    }

    public function test_post_buku_unauthorized_without_token(): void
    {
        $response = $this->postJson('/api/v1/bukus', [
            'isbn' => '9781234567890',
            'judul' => 'Test Buku',
        ]);

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
    }

    public function test_post_buku_success_with_token_returns_201_and_location_header(): void
    {
        $kategori = Kategori::first();

        $payload = [
            'isbn' => '9781234567890',
            'judul' => 'Microservices Architecture in PHP',
            'penulis' => 'Dr. Chandra Pardede',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2025,
            'kategori_id' => $kategori->id,
            'stok' => 5,
            'sinopsis' => 'Panduan arsitektur microservices tingkat lanjut.',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/bukus', $payload);

        $response->assertStatus(201);
        $this->assertTrue($response->headers->has('Location'));
        $createdId = $response->json('data.id');
        $this->assertEquals(url("/api/v1/bukus/{$createdId}"), $response->headers->get('Location'));
    }

    public function test_put_full_replacement_vs_patch_partial_update(): void
    {
        $buku = Buku::first();

        // 1. Uji PATCH: hanya ubah stok
        $patchRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->patchJson("/api/v1/bukus/{$buku->id}", [
                'stok' => 99,
            ]);

        $patchRes->assertStatus(200);
        $patchRes->assertJsonPath('data.stok', 99);
        $patchRes->assertJsonPath('data.judul', $buku->judul);

        // 2. Uji PUT: seluruh field wajib dikirim
        $kategori = Kategori::first();
        $putPayload = [
            'isbn' => $buku->isbn,
            'judul' => 'Judul Baru Setelah PUT',
            'penulis' => 'Penulis Baru',
            'penerbit' => 'Penerbit Baru',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 20,
            // sinopsis sengaja tidak dikirim -> pada PUT wajib direset ke null
        ];

        $putRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/bukus/{$buku->id}", $putPayload);

        $putRes->assertStatus(200);
        $putRes->assertJsonPath('data.judul', 'Judul Baru Setelah PUT');
        $putRes->assertJsonPath('data.sinopsis', null);
    }

    public function test_delete_buku_conflict_if_has_active_loan(): void
    {
        // Buku 1 dipinjam oleh Petra (status: dipinjam)
        $buku1 = Buku::first();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/bukus/{$buku1->id}");

        $response->assertStatus(409);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Conflict — Book Has Active Loans',
            'status' => 409,
        ]);
    }

    public function test_delete_buku_success_returns_204_no_content(): void
    {
        $kategori = Kategori::first();
        $bukuBaru = Buku::create([
            'isbn' => '9780000000001',
            'judul' => 'Buku Sementara untuk Dihapus',
            'penulis' => 'Penulis Test',
            'penerbit' => 'Penerbit Test',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 1,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/bukus/{$bukuBaru->id}");

        $response->assertStatus(204);
        $this->assertEmpty($response->getContent());
        $this->assertDatabaseMissing('bukus', ['id' => $bukuBaru->id]);
    }
}
