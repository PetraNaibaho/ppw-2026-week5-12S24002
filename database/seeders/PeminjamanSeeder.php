<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buku1 = Buku::first();
        $buku2 = Buku::skip(1)->first();

        if ($buku1) {
            // Mahasiswa 1: Memiliki 1 pinjaman aktif (status: dipinjam)
            Peminjaman::create([
                'buku_id' => $buku1->id,
                'nim_peminjam' => '12S24002',
                'nama_peminjam' => 'Petra Ignatius Pengayoman Naibaho',
                'tanggal_pinjam' => now()->subDays(3)->toDateString(),
                'durasi_hari' => 7,
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
            ]);
        }

        if ($buku2) {
            // Mahasiswa 2: Pinjaman sudah selesai/dikembalikan (status: dikembalikan -> bebas pustaka)
            Peminjaman::create([
                'buku_id' => $buku2->id,
                'nim_peminjam' => '12S22001',
                'nama_peminjam' => 'Bona Pandiangan',
                'tanggal_pinjam' => now()->subDays(10)->toDateString(),
                'durasi_hari' => 7,
                'tanggal_kembali' => now()->subDays(3)->toDateString(),
                'status' => 'dikembalikan',
            ]);
        }
    }
}
