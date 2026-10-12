<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BukuTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_url_redirects_to_buku_index(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('buku.index'));
    }

    public function test_can_render_buku_index_page(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'TIK',
            'nama_kategori' => 'Teknologi Informasi',
        ]);

        Buku::create([
            'isbn' => '9786022987101',
            'judul' => 'Pemrograman Web Modern Laravel',
            'penulis' => 'Chandro Pardede',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 10,
            'sinopsis' => 'Buku teks ajar pemrograman web modern.',
        ]);

        $response = $this->get(route('buku.index'));
        $response->assertStatus(200);
        $response->assertSee('Pemrograman Web Modern Laravel');
        $response->assertSee('Teknologi Informasi');
    }

    public function test_can_render_create_buku_page(): void
    {
        $response = $this->get(route('buku.create'));
        $response->assertStatus(200);
        $response->assertSee('Formulir Pendaftaran Buku Baru');
    }

    public function test_can_store_buku_with_valid_data(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        $payload = [
            'isbn' => '9786022987999',
            'judul' => 'Clean Code Architecture in Laravel',
            'penulis' => 'Robert C. Martin',
            'penerbit' => 'Informatika Bandung',
            'tahun_terbit' => 2023,
            'kategori_id' => $kategori->id,
            'stok' => 15,
            'sinopsis' => 'Buku panduan kode bersih dan pola arsitektur.',
        ];

        $response = $this->post(route('buku.store'), $payload);

        $response->assertRedirect(route('buku.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bukus', [
            'isbn' => '9786022987999',
            'judul' => 'Clean Code Architecture in Laravel',
        ]);
    }

    public function test_store_buku_validation_fails_for_invalid_data(): void
    {
        $response = $this->post(route('buku.store'), [
            'isbn' => '123', // kurang dari 13 digit
            'judul' => 'Abc', // kurang dari 5 karakter
            'penulis' => '',
            'penerbit' => '',
            'tahun_terbit' => 1800,
            'kategori_id' => 99999, // tidak eksis
            'stok' => -5, // negatif
        ]);

        $response->assertSessionHasErrors([
            'isbn',
            'judul',
            'penulis',
            'penerbit',
            'tahun_terbit',
            'kategori_id',
            'stok',
        ]);
    }

    public function test_can_render_buku_detail_page(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'DSN',
            'nama_kategori' => 'Sains Data',
        ]);

        $buku = Buku::create([
            'isbn' => '9786022987111',
            'judul' => 'Deep Learning Fundamentals',
            'penulis' => 'Ian Goodfellow',
            'penerbit' => 'MIT Press',
            'tahun_terbit' => 2022,
            'kategori_id' => $kategori->id,
            'stok' => 5,
            'sinopsis' => 'Kajian teoritis dan praktis jaringan syaraf tiruan.',
        ]);

        $response = $this->get(route('buku.show', $buku));
        $response->assertStatus(200);
        $response->assertSee('Deep Learning Fundamentals');
        $response->assertSee('Sains Data');
    }

    public function test_can_update_buku_successfully(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Sistem Informasi',
        ]);

        $buku = Buku::create([
            'isbn' => '9786022987222',
            'judul' => 'Analisis Sistem Informasi Lama',
            'penulis' => 'Joseph Valacich',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2020,
            'kategori_id' => $kategori->id,
            'stok' => 7,
            'sinopsis' => 'Edisi pertama analisis sistem.',
        ]);

        $response = $this->put(route('buku.update', $buku), [
            'isbn' => '9786022987222', // tetap sama, harus lolos Rule::unique ignore
            'judul' => 'Analisis Sistem Informasi Baru Edisi 2',
            'penulis' => 'Joseph Valacich & Joey George',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2022,
            'kategori_id' => $kategori->id,
            'stok' => 12,
            'sinopsis' => 'Edisi revisi terbaru analisis sistem informasi.',
        ]);

        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseHas('bukus', [
            'id' => $buku->id,
            'judul' => 'Analisis Sistem Informasi Baru Edisi 2',
            'stok' => 12,
        ]);
    }

    public function test_can_delete_buku(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'MAT',
            'nama_kategori' => 'Matematika Terapan',
        ]);

        $buku = Buku::create([
            'isbn' => '9786022987333',
            'judul' => 'Aljabar Linier dan Matriks',
            'penulis' => 'Howard Anton',
            'penerbit' => 'Erlangga',
            'tahun_terbit' => 2021,
            'kategori_id' => $kategori->id,
            'stok' => 8,
            'sinopsis' => 'Teori vektor dan aljabar matriks.',
        ]);

        $response = $this->delete(route('buku.destroy', $buku));

        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseMissing('bukus', [
            'id' => $buku->id,
        ]);
    }

    public function test_eager_loading_mitigates_n_plus_one_query(): void
    {
        $kategoriA = Kategori::create(['kode_kategori' => 'A1', 'nama_kategori' => 'Kategori A']);
        $kategoriB = Kategori::create(['kode_kategori' => 'B1', 'nama_kategori' => 'Kategori B']);

        for ($i = 1; $i <= 10; $i++) {
            Buku::create([
                'isbn' => sprintf('9786022987%03d', $i),
                'judul' => "Buku Uji Performa Ke-{$i}",
                'penulis' => "Penulis {$i}",
                'penerbit' => "Penerbit {$i}",
                'tahun_terbit' => 2024,
                'kategori_id' => ($i % 2 === 0) ? $kategoriA->id : $kategoriB->id,
                'stok' => $i,
                'sinopsis' => "Sinopsis buku ke-{$i}",
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        // Eksekusi dengan Eager Loading
        $books = Buku::with('kategori')->get();

        // Akses relasi pada setiap iterasi looping
        foreach ($books as $b) {
            $namaKategori = $b->kategori->nama_kategori;
            $this->assertNotEmpty($namaKategori);
        }

        $queryCount = count(DB::getQueryLog());

        // Dengan Eager Loading, kueri harus TEPAT 2 (1 ke bukus, 1 ke kategoris dengan WHERE IN)
        // Tanpa Eager Loading (Lazy Loading), kuerinya akan berjumlah 11 (1 + 10 = N+1)
        $this->assertEquals(2, $queryCount, 'Eager Loading harus tepat mengeksekusi 2 kueri SQL!');
    }
}
