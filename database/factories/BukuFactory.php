<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Buku>
 */
class BukuFactory extends Factory
{
    protected $model = Buku::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publishers = [
            'Informatika Bandung',
            'Andi Publisher Yogyakarta',
            'Del Press Laguboti',
            'Elex Media Komputindo',
            'Gramedia Pustaka Utama',
            'Deepublish Sleman',
            'Gava Media',
            'Politeknik Negeri Media Kreatif',
        ];

        $authors = [
            'Dr. Eng. Arnaldo Marulitua Sinaga, S.T., M.InfoTech.',
            'Chandro Pardede, S.Kom., M.Sc.',
            'Iustisia Natalia Simbolon, S.Kom., M.T.',
            'Dr. Johannes Harungguan Sianipar, S.T., M.T.',
            'Tenno Martin Tampubolon, S.T., M.Sc.',
            'Riyanthi Anggrainy, S.Kom., M.Cs.',
            'Samuel Pandiangan, S.T., M.Kom.',
            'Togar Alam Napitupulu, M.Sc., Ph.D.',
        ];

        $titles = [
            'Arsitektur Web Modern dan Framework Laravel',
            'Pemrograman Berorientasi Objek Lanjutan dengan PHP',
            'Rekayasa Kebutuhan Perangkat Lunak Berbasis Agile',
            'Struktur Data dan Algoritma Pemrograman Tingkat Lanjut',
            'Pengantar Sains Data dan Analisis Statistik Modern',
            'Keamanan Jaringan Komputer dan Kriptografi Terapan',
            'Desain Basis Data Relasional dan Optimasi Kueri SQL',
            'Penerapan Kecerdasan Buatan dalam Diagnostik Medis',
            'Interaksi Manusia dan Komputer: Teori dan Desain UX',
            'Pemrograman Fullstack Menggunakan Laravel dan React',
            'Sistem Informasi Enterprise dan Perencanaan Sumber Daya',
            'Komputasi Awan dan Otomasi DevOps untuk Pemula',
            'Analisis Pola Desain Perangkat Lunak (Design Patterns)',
            'Teknik Pengujian Perangkat Lunak Otomatis (TDD)',
            'Manajemen Proyek TI Berstandar PMBOK dan Scrum',
            'Prinsip Sistem Operasi dan Manajemen Sumber Daya',
            'Data Mining: Konsep dan Aplikasi Bisnis Cerdas',
            'Pengolahan Citra Digital dan Pengenalan Pola Citra',
            'Etika Profesi Informatika dan Regulasi Siber',
            'Metodologi Penelitian Bidang Ilmu Komputer & TI',
            'Dasar-Dasar Jaringan Komputer dan Pemrograman Socket',
            'Pengembangan Aplikasi Mobile Lintas Platform',
            'Deep Learning untuk Computer Vision dan Natural Language',
            'Tata Kelola Teknologi Informasi Berbasis COBIT 2019',
        ];

        return [
            'isbn' => '978' . fake()->unique()->numerify('##########'),
            'judul' => fake()->randomElement($titles) . ' ' . fake()->optional(0.3)->passthrough('Edisi ' . fake()->numberBetween(2, 5)),
            'penulis' => fake()->randomElement($authors),
            'penerbit' => fake()->randomElement($publishers),
            'tahun_terbit' => fake()->numberBetween(2018, 2026),
            'kategori_id' => Kategori::factory(),
            'stok' => fake()->numberBetween(3, 45),
            'sinopsis' => fake()->paragraph(4),
        ];
    }
}
