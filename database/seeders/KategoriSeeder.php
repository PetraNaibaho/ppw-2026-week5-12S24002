<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            [
                'kode_kategori' => 'RPL',
                'nama_kategori' => 'Rekayasa Perangkat Lunak',
            ],
            [
                'kode_kategori' => 'TIK',
                'nama_kategori' => 'Teknologi Informasi & Komputer',
            ],
            [
                'kode_kategori' => 'DSN',
                'nama_kategori' => 'Sains Data & Kecerdasan Buatan',
            ],
            [
                'kode_kategori' => 'JARKOM',
                'nama_kategori' => 'Jaringan & Keamanan Siber',
            ],
            [
                'kode_kategori' => 'SI',
                'nama_kategori' => 'Sistem Informasi Manajemen',
            ],
            [
                'kode_kategori' => 'MAT-ALG',
                'nama_kategori' => 'Matematika & Teori Komputasi',
            ],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::updateOrCreate(
                ['kode_kategori' => $kategori['kode_kategori']],
                ['nama_kategori' => $kategori['nama_kategori']]
            );
        }
    }
}
