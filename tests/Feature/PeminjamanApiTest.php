<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanApiTest extends TestCase
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

    public function test_get_peminjamans_list_with_filters(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/peminjamans?status=dipinjam');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_post_peminjaman_success_decrements_stock_and_returns_201(): void
    {
        $buku = Buku::where('stok', '>', 0)->first();
        $initialStock = $buku->stok;

        $payload = [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24099',
            'nama_peminjam' => 'Mahasiswa Baru',
            'durasi_hari' => 7,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', $payload);

        $response->assertStatus(201);
        $this->assertTrue($response->headers->has('Location'));
        $this->assertEquals($initialStock - 1, $buku->fresh()->stok);

        // Periksa tautan dinamis HATEOAS: kembalikan_buku harus tersedia
        $this->assertArrayHasKey('kembalikan_buku', $response->json('data._links'));
    }

    public function test_post_peminjaman_rejects_when_stock_exhausted_422(): void
    {
        $kategori = Kategori::first();
        $bukuHabis = Buku::create([
            'isbn' => '9789999999999',
            'judul' => 'Buku Habis Stok',
            'penulis' => 'Penulis X',
            'penerbit' => 'Penerbit Y',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 0,
        ]);

        $payload = [
            'buku_id' => $bukuHabis->id,
            'nim_peminjam' => '12S24099',
            'nama_peminjam' => 'Mahasiswa X',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', $payload);

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Book Stock Exhausted',
            'status' => 422,
        ]);
    }

    public function test_post_peminjaman_rejects_duplicate_active_loan_409(): void
    {
        // Di seeder, Petra (12S24002) sudah meminjam buku 1 (status: dipinjam)
        $buku1 = Buku::first();

        $payload = [
            'buku_id' => $buku1->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Petra Ignatius Pengayoman Naibaho',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', $payload);

        $response->assertStatus(409);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Duplicate Active Loan Conflict',
            'status' => 409,
        ]);
    }

    public function test_patch_kembalikan_success_increments_stock_and_updates_hateoas(): void
    {
        $peminjaman = Peminjaman::where('status', 'dipinjam')->first();
        $buku = $peminjaman->buku;
        $initialStock = $buku->stok;

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->patchJson("/api/v1/peminjamans/{$peminjaman->id}/kembalikan");

        $response->assertStatus(200);
        $this->assertEquals($initialStock + 1, $buku->fresh()->stok);
        $this->assertEquals('dikembalikan', $peminjaman->fresh()->status);
        $this->assertNotNull($peminjaman->fresh()->tanggal_kembali);

        // Keindahan HATEOAS: Setelah dikembalikan, link kembalikan_buku HILANG
        $this->assertArrayNotHasKey('kembalikan_buku', $response->json('data._links'));
    }

    public function test_patch_kembalikan_rejects_already_returned_loan_409(): void
    {
        // Di seeder, Bona (12S22001) sudah berstatus 'dikembalikan'
        $peminjamanReturned = Peminjaman::where('status', 'dikembalikan')->first();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->patchJson("/api/v1/peminjamans/{$peminjamanReturned->id}/kembalikan");

        $response->assertStatus(409);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Conflict — Book Already Returned',
            'status' => 409,
        ]);
    }
}
