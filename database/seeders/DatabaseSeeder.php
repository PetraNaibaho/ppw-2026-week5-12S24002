<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan Seeder Kategori dan Buku (SIPUS-Del)
        $this->call([
            KategoriSeeder::class,
            BukuSeeder::class,
        ]);
    }
}
