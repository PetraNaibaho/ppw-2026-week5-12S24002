<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pustakawan Administrator untuk Pengujian API & Sanctum Auth
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name' => 'Pustakawan SIPUS-Del',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Jalankan Seeder Kategori, Buku, dan Peminjaman
        $this->call([
            KategoriSeeder::class,
            BukuSeeder::class,
            PeminjamanSeeder::class,
        ]);
    }
}
