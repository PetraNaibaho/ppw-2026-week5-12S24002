<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kategori>
 */
class KategoriFactory extends Factory
{
    protected $model = Kategori::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            ['kode' => 'TIK', 'nama' => 'Teknologi Informasi & Komputer'],
            ['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak'],
            ['kode' => 'DSN', 'nama' => 'Sains Data & Kecerdasan Buatan'],
            ['kode' => 'JAR', 'nama' => 'Jaringan & Keamanan Siber'],
            ['kode' => 'SIS', 'nama' => 'Sistem Informasi Manajemen'],
            ['kode' => 'MAT', 'nama' => 'Matematika & Algoritma'],
            ['kode' => 'BIS', 'nama' => 'Bisnis Digital & Kewirausahaan'],
            ['kode' => 'LIT', 'nama' => 'Literasi & Humaniora'],
        ];

        $choice = fake()->unique()->randomElement($categories);

        return [
            'kode_kategori' => $choice['kode'],
            'nama_kategori' => $choice['nama'],
        ];
    }
}
