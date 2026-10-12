<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_kategori_index_page(): void
    {
        Kategori::create([
            'kode_kategori' => 'RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        $response = $this->get(route('kategori.index'));
        $response->assertStatus(200);
        $response->assertSee('Rekayasa Perangkat Lunak');
        $response->assertSee('RPL');
    }

    public function test_can_render_create_kategori_page(): void
    {
        $response = $this->get(route('kategori.create'));
        $response->assertStatus(200);
        $response->assertSee('Formulir Tambah Kategori');
    }

    public function test_can_store_kategori_with_valid_data(): void
    {
        $payload = [
            'kode_kategori' => 'CYBER',
            'nama_kategori' => 'Keamanan Siber & Forensik',
        ];

        $response = $this->post(route('kategori.store'), $payload);

        $response->assertRedirect(route('kategori.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('kategoris', [
            'kode_kategori' => 'CYBER',
            'nama_kategori' => 'Keamanan Siber & Forensik',
        ]);
    }

    public function test_store_kategori_fails_validation(): void
    {
        Kategori::create([
            'kode_kategori' => 'TEST',
            'nama_kategori' => 'Kategori Duplikat',
        ]);

        $response = $this->post(route('kategori.store'), [
            'kode_kategori' => 'TEST', // Duplikat
            'nama_kategori' => 'AB', // Terlalu pendek (< 3)
        ]);

        $response->assertSessionHasErrors(['kode_kategori', 'nama_kategori']);
    }

    public function test_can_render_kategori_show_page_with_related_books(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'AI',
            'nama_kategori' => 'Artificial Intelligence',
        ]);

        $buku = Buku::create([
            'isbn' => '9786022987111',
            'judul' => 'Prinsip AI Modern',
            'penulis' => 'Stuart Russell',
            'penerbit' => 'Pearson',
            'tahun_terbit' => 2021,
            'kategori_id' => $kategori->id,
            'stok' => 4,
            'sinopsis' => 'Buku teks standar AI dunia.',
        ]);

        $response = $this->get(route('kategori.show', $kategori));
        $response->assertStatus(200);
        $response->assertSee('Artificial Intelligence');
        $response->assertSee('Prinsip AI Modern');
    }

    public function test_can_update_kategori(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'OLD',
            'nama_kategori' => 'Nama Kategori Lama',
        ]);

        $response = $this->put(route('kategori.update', $kategori), [
            'kode_kategori' => 'OLD', // Ignore self unique
            'nama_kategori' => 'Nama Kategori Baru Diperbarui',
        ]);

        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseHas('kategoris', [
            'id' => $kategori->id,
            'nama_kategori' => 'Nama Kategori Baru Diperbarui',
        ]);
    }

    public function test_can_delete_kategori(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'DEL',
            'nama_kategori' => 'Kategori Siap Dihapus',
        ]);

        $response = $this->delete(route('kategori.destroy', $kategori));

        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseMissing('kategoris', [
            'id' => $kategori->id,
        ]);
    }
}
