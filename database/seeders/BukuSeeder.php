<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriMap = Kategori::pluck('id', 'kode_kategori')->toArray();

        $books = [
            // RPL
            [
                'isbn' => '9786022987101',
                'judul' => 'Clean Code: Panduan Praktis Pengembangan Perangkat Lunak Tangkas',
                'penulis' => 'Robert C. Martin (Terj. Tim Dosen Del)',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2021,
                'kode_kategori' => 'RPL',
                'stok' => 12,
                'sinopsis' => 'Buku pegangan wajib tentang bagaimana menulis kode program yang bersih, modular, mudah dibaca, serta berdaya uji tinggi melalui prinsip refactoring.',
            ],
            [
                'isbn' => '9786022987102',
                'judul' => 'Pola Desain Gang of Four: Konsep dan Implementasi Berorientasi Objek',
                'penulis' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'penerbit' => 'Andi Publisher Yogyakarta',
                'tahun_terbit' => 2020,
                'kode_kategori' => 'RPL',
                'stok' => 8,
                'sinopsis' => 'Katalog lengkap 23 pola desain klasik (Creational, Structural, Behavioral) untuk arsitektur aplikasi berskala perusahaan.',
            ],
            [
                'isbn' => '9786022987103',
                'judul' => 'Arsitektur Perangkat Lunak Modern: Dari Monolit ke Microservices',
                'penulis' => 'Chandro Pardede, S.Kom., M.Sc.',
                'penerbit' => 'Del Press Laguboti',
                'tahun_terbit' => 2024,
                'kode_kategori' => 'RPL',
                'stok' => 15,
                'sinopsis' => 'Kajian komprehensif transformasi arsitektur perangkat lunak dari pola MVC terpadu menuju service-oriented dan event-driven microservices.',
            ],
            [
                'isbn' => '9786022987104',
                'judul' => 'Pengujian Perangkat Lunak dan CI/CD Pipeline Otomatis',
                'penulis' => 'Tenno Martin Tampubolon, S.T., M.Sc.',
                'penerbit' => 'Del Press Laguboti',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'RPL',
                'stok' => 10,
                'sinopsis' => 'Teknik penyusunan Test-Driven Development (TDD), pengujian unit, integrasi, dan otomatisasi deployment dengan GitHub Actions.',
            ],

            // TIK
            [
                'isbn' => '9786022987105',
                'judul' => 'Mastering Laravel 11 & Arsitektur Model-View-Controller',
                'penulis' => 'Dr. Johannes Harungguan Sianipar, S.T., M.T.',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2024,
                'kode_kategori' => 'TIK',
                'stok' => 20,
                'sinopsis' => 'Panduan tuntas rekayasa web modern berbasis PHP 8.2+, Eloquent ORM, Blade templating, dan mitigasi bottleneck performa basis data.',
            ],
            [
                'isbn' => '9786022987106',
                'judul' => 'Prinsip Dasar Sistem Operasi Modern dan Komputasi Konkuren',
                'penulis' => 'Abraham Silberschatz & Peter B. Galvin',
                'penerbit' => 'Elex Media Komputindo',
                'tahun_terbit' => 2022,
                'kode_kategori' => 'TIK',
                'stok' => 9,
                'sinopsis' => 'Kajian mendalam mengenai manajemen memori, penjadwalan proses, konkurensi thread, file system, dan proteksi kernel.',
            ],
            [
                'isbn' => '9786022987107',
                'judul' => 'Pemrograman Full-Stack Modern dengan REST API dan TypeScript',
                'penulis' => 'Samuel Pandiangan, S.T., M.Kom.',
                'penerbit' => 'Deepublish Sleman',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'TIK',
                'stok' => 14,
                'sinopsis' => 'Integrasi backend API berbasis Laravel dan Node.js dengan antarmuka frontend reaktif TypeScript dan Vite.',
            ],
            [
                'isbn' => '9786022987108',
                'judul' => 'Desain Antarmuka Pengguna dan Pengalaman Pengguna (UI/UX) Terapan',
                'penulis' => 'Iustisia Natalia Simbolon, S.Kom., M.T.',
                'penerbit' => 'Del Press Laguboti',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'TIK',
                'stok' => 11,
                'sinopsis' => 'Penerapan Design Thinking, wireframing, prototipe interaktif, dan evaluasi heuristik pada aplikasi web enterprise.',
            ],

            // DSN
            [
                'isbn' => '9786022987109',
                'judul' => 'Pengantar Kecerdasan Buatan dan Sistem Pakar Modern',
                'penulis' => 'Dr. Eng. Arnaldo Marulitua Sinaga, S.T., M.InfoTech.',
                'penerbit' => 'Del Press Laguboti',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'DSN',
                'stok' => 18,
                'sinopsis' => 'Fondasi algoritma pencarian kecerdasan buatan, inferensi logika, probabilistic reasoning, dan implementasi machine learning pemula.',
            ],
            [
                'isbn' => '9786022987110',
                'judul' => 'Data Science Terapan: Eksplorasi, Visualisasi, dan Pemodelan Prediktif',
                'penulis' => 'Riyanthi Anggrainy, S.Kom., M.Cs.',
                'penerbit' => 'Andi Publisher Yogyakarta',
                'tahun_terbit' => 2024,
                'kode_kategori' => 'DSN',
                'stok' => 16,
                'sinopsis' => 'Metode analisis data statistik eksploratori menggunakan pustaka komputasi Python dan integrasi visualisasi data interaktif.',
            ],
            [
                'isbn' => '9786022987111',
                'judul' => 'Deep Learning untuk Pemrosesan Bahasa Alami dan Visi Komputer',
                'penulis' => 'Ian Goodfellow & Yoshua Bengio',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2022,
                'kode_kategori' => 'DSN',
                'stok' => 7,
                'sinopsis' => 'Arsitektur Artificial Neural Networks, Convolutional Neural Networks (CNN), Transformer, dan Large Language Models.',
            ],
            [
                'isbn' => '9786022987112',
                'judul' => 'Big Data Analytics: Arsitektur Ekosistem Hadoop dan Apache Spark',
                'penulis' => 'Togar Alam Napitupulu, M.Sc., Ph.D.',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2021,
                'kode_kategori' => 'DSN',
                'stok' => 6,
                'sinopsis' => 'Pengelolaan data berskala terabyte hingga petabyte secara paralel dan terdistribusi untuk analitik bisnis cerdas.',
            ],

            // JARKOM
            [
                'isbn' => '9786022987113',
                'judul' => 'Topologi Jaringan Komputer dan Routing Tingkat Lanjut',
                'penulis' => 'Andrew S. Tanenbaum & David J. Wetherall',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2020,
                'kode_kategori' => 'JARKOM',
                'stok' => 13,
                'sinopsis' => 'Analisis mendalam protokol OSI layer, TCP/IP, BGP routing, subnetting VLSM, dan troubleshooting lalu lintas data.',
            ],
            [
                'isbn' => '9786022987114',
                'judul' => 'Keamanan Siber Terapan: Ethical Hacking dan Uji Penetrasi',
                'penulis' => 'Bambang Sugiantoro & Tim Keamanan Del',
                'penerbit' => 'Gava Media',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'JARKOM',
                'stok' => 9,
                'sinopsis' => 'Metodologi audit keamanan sistem jaringan, analisis kerentanan OWASP Top 10, pertahanan firewall, dan kriptografi simetris/asimetris.',
            ],
            [
                'isbn' => '9786022987115',
                'judul' => 'Komputasi Awan AWS & Azure: Desain Infrastruktur Skalabilitas Tinggi',
                'penulis' => 'Heru Wijayanto, S.T., M.Kom.',
                'penerbit' => 'Deepublish Sleman',
                'tahun_terbit' => 2024,
                'kode_kategori' => 'JARKOM',
                'stok' => 12,
                'sinopsis' => 'Prinsip Infrastructure as Code (IaC), virtual private cloud, autoscaling, load balancer, dan strategi disaster recovery.',
            ],
            [
                'isbn' => '9786022987116',
                'judul' => 'Internet of Things (IoT): Protokol MQTT dan Sensor Cerdas',
                'penulis' => 'Dr. Johannes Harungguan Sianipar, S.T., M.T.',
                'penerbit' => 'Del Press Laguboti',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'JARKOM',
                'stok' => 8,
                'sinopsis' => 'Integrasi mikrokontroler ESP32, arsitektur edge computing, dan pengiriman telemetri data real-time berbasis broker MQTT.',
            ],

            // SI
            [
                'isbn' => '9786022987117',
                'judul' => 'Manajemen Sistem Informasi Korporat Berbasis ERP SAP',
                'penulis' => 'Kenneth C. Laudon & Jane P. Laudon',
                'penerbit' => 'Elex Media Komputindo',
                'tahun_terbit' => 2021,
                'kode_kategori' => 'SI',
                'stok' => 15,
                'sinopsis' => 'Konsep integrasi rantai pasok (SCM), manajemen relasi pelanggan (CRM), dan pengambilan keputusan strategis tingkat eksekutif.',
            ],
            [
                'isbn' => '9786022987118',
                'judul' => 'Tata Kelola TI Berstandar COBIT 2019 dan ITIL 4 Foundation',
                'penulis' => 'Togar Alam Napitupulu, M.Sc., Ph.D.',
                'penerbit' => 'Andi Publisher Yogyakarta',
                'tahun_terbit' => 2022,
                'kode_kategori' => 'SI',
                'stok' => 11,
                'sinopsis' => 'Kerangka kerja mitigasi risiko tata kelola TI perusahaan, alignment strategi bisnis, dan jaminan kepatuhan regulasi.',
            ],
            [
                'isbn' => '9786022987119',
                'judul' => 'Analisis dan Perancangan Sistem Berorientasi Objek dengan UML 2.5',
                'penulis' => 'Joseph Valacich & Joey George',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2020,
                'kode_kategori' => 'SI',
                'stok' => 17,
                'sinopsis' => 'Panduan terstruktur pembuatan use case diagram, sequence diagram, activity diagram, dan class diagram dalam rekayasa sistem informasi.',
            ],
            [
                'isbn' => '9786022987120',
                'judul' => 'E-Commerce Strategy & FinTech Innovation di Era Industri 4.0',
                'penulis' => 'Iustisia Natalia Simbolon, S.Kom., M.T.',
                'penerbit' => 'Deepublish Sleman',
                'tahun_terbit' => 2024,
                'kode_kategori' => 'SI',
                'stok' => 10,
                'sinopsis' => 'Peluang dan dinamika bisnis pembayaran digital, keamanan transaksi moneter elektronik, dan arsitektur marketplace.',
            ],

            // MAT-ALG
            [
                'isbn' => '9786022987121',
                'judul' => 'Matematika Diskrit dan Aplikasinya pada Ilmu Komputer',
                'penulis' => 'Kenneth H. Rosen',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2021,
                'kode_kategori' => 'MAT-ALG',
                'stok' => 22,
                'sinopsis' => 'Teori himpunan, relasi rekurensi, kombinatorika, teori graf, pohon keputusan, dan aljabar boolean untuk rekayasa algoritma.',
            ],
            [
                'isbn' => '9786022987122',
                'judul' => 'Pengantar Algoritma dan Struktur Data Efisien (Introduction to Algorithms)',
                'penulis' => 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest',
                'penerbit' => 'Andi Publisher Yogyakarta',
                'tahun_terbit' => 2022,
                'kode_kategori' => 'MAT-ALG',
                'stok' => 16,
                'sinopsis' => 'Analisis kompleksitas asimtotik Big-O, sorting lanjut, dynamic programming, greedy methods, dan graf jalur terpendek Dijkstra.',
            ],
            [
                'isbn' => '9786022987123',
                'judul' => 'Aljabar Linier Elementer untuk Kecerdasan Buatan dan Grafika Komputer',
                'penulis' => 'Howard Anton & Chris Rorres',
                'penerbit' => 'Elex Media Komputindo',
                'tahun_terbit' => 2020,
                'kode_kategori' => 'MAT-ALG',
                'stok' => 14,
                'sinopsis' => 'Operasi matriks, eliminasi Gauss-Jordan, determinan, vektor eigen, nilai eigen, serta dekomposisi Singular Value (SVD).',
            ],
            [
                'isbn' => '9786022987124',
                'judul' => 'Probabilitas dan Statistika Inferensial untuk Rekayasawan',
                'penulis' => 'Ronald E. Walpole & Raymond H. Myers',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'MAT-ALG',
                'stok' => 12,
                'sinopsis' => 'Distribusi probabilitas diskrit dan kontinu, uji hipotesis parameter, interval kepercayaan, dan regresi linier berganda.',
            ],
        ];

        foreach ($books as $item) {
            $kategoriId = $kategoriMap[$item['kode_kategori']] ?? null;

            if ($kategoriId) {
                Buku::updateOrCreate(
                    ['isbn' => $item['isbn']],
                    [
                        'judul' => $item['judul'],
                        'penulis' => $item['penulis'],
                        'penerbit' => $item['penerbit'],
                        'tahun_terbit' => $item['tahun_terbit'],
                        'kategori_id' => $kategoriId,
                        'stok' => $item['stok'],
                        'sinopsis' => $item['sinopsis'],
                    ]
                );
            }
        }
    }
}
