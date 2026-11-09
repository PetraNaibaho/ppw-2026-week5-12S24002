# Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del)
### Modul Praktikum Minggu 09 — Arsitektur Web Services & REST API
**Institut Teknologi Del &bull; Fakultas Informatika dan Teknik Elektro**  
**Program Studi Sarjana Sistem Informasi / Informatika**

---

## I. Informasi Mahasiswa & Proyek

| Atribut | Keterangan |
| :--- | :--- |
| **Nama Mahasiswa** | Petra Ignatius Pengayoman Naibaho |
| **NIM** | 12S24002 |
| **Kelas** | 12SI1 |
| **Program Studi** | Sarjana Sistem Informasi |
| **Mata Kuliah** | Pemrograman & Pengujian Aplikasi Web (12S3101) |
| **Dosen Pengampu** | Chandro Pardede, S.Kom., M.Sc. |
| **Tahun Akademik** | Semester Genap 2026/2027 |
| **Branch Pengerjaan** | `week9-rest-api` |
| **Repositori GitHub** | `ppw-2026-week5-12S24002` |

---

## II. Analisis Kebutuhan & Kelayakan Web Services (Feasibility Analysis)

### 2.1 Latar Belakang Integrasi Sistem Antar-Mesin (Machine-to-Machine)
Pada fase monolitik (Minggu 1 s.d. Minggu 6), SIPUS-Del dibangun dengan pola *Server-Side Rendering* (SSR) yang mengembalikan tampilan visual HTML + CSS Blade (~120 KB) untuk pengguna manusia via peramban web. Namun, dalam ekosistem kampus modern IT Del, perpustakaan dituntut untuk terhubung dengan berbagai platform heterogen:
1. **Sistem Bebas Pustaka Yudisium (Legacy Core System)**: Sistem kampus lama berbasis Java/SOAP yang membutuhkan integrasi nir-antarmuka manusia untuk memvalidasi apakah mahasiswa masih memiliki tanggungan peminjaman buku sebelum wisuda.
2. **Aplikasi Mobile Mahasiswa & Kiosk Mandiri**: Aplikasi klien modern berbasis Flutter/Android yang membutuhkan pertukaran data ringan, responsif, dan nir-status (*stateless*).
3. **Pengayaan Metadata Koleksi Eksternal**: Kebutuhan mengonsumsi layanan katalog buku publik global (*OpenLibrary REST API*) dan repositori kode portofolio mahasiswa (*GitHub REST API*).

### 2.2 Matriks Peran Layanan pada SIPUS-Del (Consumer vs Provider)

| Komponen / Fitur | Peran Arsitektural | Protokol & Format | Alasan Pemilihan Arsitektur |
| :--- | :--- | :--- | :--- |
| **Layanan Bebas Pustaka Yudisium** (`/soap/sipus`) | **Service Provider** | SOAP 1.1 / XML (`<soap:Envelope>`) over HTTP POST | Menjaga kompatibilitas dengan sistem administrasi yudisium kampus *legacy* yang dibangun berbasis WSDL formal tanpa perlu merombak sistem lama. |
| **Katalog Buku & Kategori** (`/api/v1/bukus`) | **Service Provider** | RESTful Web API (JSON, RFC 7807, HATEOAS) | Ringan, mudah dikonsumsi aplikasi mobile/frontend SPA modern, mendukung paginasi efisien dan pemfilteran fleksibel. |
| **Sirkulasi Peminjaman ACID** (`/api/v1/peminjamans`) | **Service Provider** | RESTful Web API (JSON + Sanctum Bearer Token) | Stateless Bearer Token memudahkan integrasi mesin kiosk mandiri, dijamin integritas kuotanya melalui transaksi ACID dengan *pessimistic locking*. |
| **Pengayaan Metadata Buku** (`/api/v1/bukus/{id}/referensi-eksternal`) | **Service Consumer** | REST Client (JSON over HTTP via `Http` Facade) | Mengonsumsi data katalog OpenLibrary API eksternal secara asinkron tanpa membebani penyimpanan basis data lokal. |
| **Portofolio Mahasiswa** (`/api/v1/portfolios/github/{username}`) | **Service Consumer** | REST Client (JSON v3 over HTTP via `Http` Facade) | Mengorkestrasi data profil dan repositori publik mahasiswa langsung dari GitHub REST API. |

### 2.3 Komparasi Mendalam: SOAP Web Service vs RESTful Web API

| Dimensi Evaluasi | SOAP Web Service (Legacy Yudisium) | RESTful Web API (Mobile & Kiosk v1) | Alasan Teknis Pemilihan pada SIPUS-Del |
| :--- | :--- | :--- | :--- |
| **Gaya Desain** | Protokol komunikasi formal berstandar W3C ketat | Gaya arsitektur (*architectural style*) berbasis semantik HTTP murni | REST memberikan fleksibilitas tinggi bagi tim pengembang frontend & mobile tanpa keterikatan protokol kaku. |
| **Format Payload** | Wajib XML baku (`<soap:Envelope>`, `<soap:Body>`, `<soap:Fault>`) | Dominan JSON (`application/json`, RFC 7807) | Ukuran payload JSON 5–8x lebih kecil dibanding XML SOAP, menghemat kuota jaringan dan CPU mobile. |
| **Kontrak Layanan** | Kontrak formal kaku via dokumen XML WSDL (`.wsdl`) | Dokumentasi antarmuka via OpenAPI 3.0 (Swagger) | OpenAPI 3.0 mudah diintegrasikan dengan Postman, Swagger UI, dan *code generator* modern. |
| **Operasi & Verbs** | Seluruh aksi di-*tunnel* melalui metode **POST** | Memanfaatkan kata kerja resmi HTTP: **GET, POST, PUT, PATCH, DELETE** | Semantik REST menjelaskan maksud operasi secara transparan langsung pada baris pertama HTTP request. |
| **Manajemen Sesi** | Dapat stateful (WS-Session) atau stateless | **Murni Stateless** (Stateless Bearer Token) | Server tidak menyimpan sesi klien di memori server (*Zero Server Session*), sehingga horizontal scaling siap dipasang di balik *load balancer*. |
| **Rekomendasi Kasus** | Sistem perbankan, yudisium kampus legacy, B2B enterprise kaku | Aplikasi mobile, web SPA (React/Vue), kiosk mandiri, microservices | REST dipilih untuk katalog & sirkulasi; SOAP dipertahankan murni sebagai gerbang kompatibilitas yudisium. |

---

## III. Arsitektur RESTful, HATEOAS, RFC 7807, & Transaksi ACID

### 3.1 Richardson Maturity Model (RMM Level 3 - Puncak REST)
SIPUS-Del API dirancang memenuhi level tertinggi pada model kematangan Leonard Richardson:
- **Level 0 (The Swamp of POX)**: Dihindari sepenuhnya.
- **Level 1 (Resources)**: Memecah URI berdasarkan kata benda jamak (`/api/v1/bukus`, `/api/v1/peminjamans`).
- **Level 2 (HTTP Verbs & Status Codes)**: Menggunakan kata kerja resmi HTTP beserta kode status semantik (`200 OK`, `201 Created` + header `Location`, `204 No Content`, `401 Unauthorized`, `404 Not Found`, `409 Conflict`, `422 Unprocessable Content`).
- **Level 3 (Hypermedia Controls / HATEOAS)**: Setiap representasi JSON menyertakan navigasi tautan dinamis `_links`:
  - **Pada `BukuResource`**: Link aksi `pinjam_buku` (`POST /api/v1/peminjamans`) **HANYA** muncul jika stok buku $> 0$. Saat stok habis ($0$), tautan tersebut otomatis hilang!
  - **Pada `PeminjamanResource`**: Link aksi `kembalikan_buku` (`PATCH /api/v1/peminjamans/{id}/kembalikan`) **HANYA** muncul jika transaksi berstatus `'dipinjam'`. Begitu buku dikembalikan, link tersebut otomatis ditiadakan.

### 3.2 Standarisasi Penanganan Galat: IETF RFC 7807 (Problem Details)
Seluruh respons kesalahan pada jalur `/api/*` distandarkan secara seragam menggunakan *Content-Type* `application/problem+json` dengan struktur baku:
```json
{
  "type": "http://localhost:8000/docs/errors/validation-failed",
  "title": "Unprocessable Content",
  "status": 422,
  "detail": "Satu atau lebih atribut input yang dikirim tidak memenuhi aturan validasi.",
  "instance": "/api/v1/bukus",
  "errors": {
    "isbn": ["The isbn field is required."]
  }
}
```

### 3.3 Semantik HTTP: Pembuktian Nyata Perbedaan PUT vs PATCH
- **PUT (`/api/v1/bukus/{id}`) — Full Replacement**: Klien **wajib** mengirimkan seluruh representasi entitas secara utuh. Jika atribut opsional yang bernilai nullable (misal `sinopsis`) tidak dikirimkan, nilai atribut tersebut akan direset menjadi `null`.
- **PATCH (`/api/v1/bukus/{id}`) — Partial Update**: Klien **cukup** mengirimkan atribut yang ingin dimodifikasi saja (menggunakan aturan validasi `sometimes|required`, misalnya hanya `{"stok": 25}`). Kolom lainnya di basis data tetap aman dan tidak disentuh.

### 3.4 Transaksi Sirkulasi Peminjaman ACID & Anti Race Condition
Proses peminjaman dan pengembalian buku mengeksekusi operasi kritis stok yang rentan terhadap *race condition*. SIPUS-Del menerapkan prinsip ACID (*Atomicity, Consistency, Isolation, Durability*) menggunakan:
```php
DB::transaction(function () use ($validated) {
    // Pessimistic Locking untuk mengunci baris buku hingga transaksi commit
    $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();
    
    if ($buku->stok < 1) {
        return $this->problemResponse('Book Stock Exhausted', 'Stok fisik buku habis.', 422, 'stock-exhausted');
    }
    
    // Cek duplikasi peminjaman aktif oleh mahasiswa yang sama
    $alreadyBorrowing = Peminjaman::where('buku_id', $buku->id)
        ->where('nim_peminjam', $nim)
        ->where('status', 'dipinjam')
        ->exists();
        
    if ($alreadyBorrowing) {
        return $this->problemResponse('Duplicate Active Loan Conflict', 'Mahasiswa masih meminjam buku ini.', 409, 'duplicate-active-loan');
    }
    
    $buku->decrement('stok');
    $peminjaman = Peminjaman::create([...]);
    return $this->createdResponse(new PeminjamanResource($peminjaman), ...);
});
```

---

## IV. Tabel Katalog Endpoint Web Services (SOAP & REST API v1)

| Metode | URI Endpoint | Proteksi Auth | Deskripsi Bisnis Layanan | Kode Status Respons |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/soap/sipus.wsdl` | Publik | Menyajikan dokumen kontrak XML WSDL layanan bebas pustaka yudisium. | `200 OK` (XML WSDL) |
| **POST** | `/soap/sipus` | Publik (No CSRF) | Memproses pengecekan status tanggungan pinjaman mahasiswa berbasis SOAP Envelope & Fault. | `200 OK` / `500 Fault` (XML) |
| **POST** | `/api/v1/auth/login` | Publik | Verifikasi kredensial pustakawan & menerbitkan Sanctum Bearer Token. | `200 OK`, `401 Unauthorized` |
| **GET** | `/api/v1/auth/me` | Bearer Token | Mengambil data profil pustakawan pemilik access token aktif. | `200 OK`, `401 Unauthorized` |
| **POST** | `/api/v1/auth/logout` | Bearer Token | Mencabut (*revoke*) token aktif dari database dan mengakhiri sesi. | `200 OK`, `401 Unauthorized` |
| **GET** | `/api/v1/bukus` | Publik | Menampilkan koleksi katalog buku terpaginasi (pencarian, filter stok/kategori, sorting, HATEOAS). | `200 OK` |
| **GET** | `/api/v1/bukus/{id}` | Publik | Menampilkan detail satu buku berdasarkan ID atau ISBN beserta riwayat pinjaman & HATEOAS. | `200 OK`, `404 Not Found` |
| **POST** | `/api/v1/bukus` | Bearer Token | Menambahkan buku baru ke katalog perpustakaan. | `201 Created` + `Location`, `401`, `422` |
| **PUT** | `/api/v1/bukus/{id}` | Bearer Token | Mengganti seluruh representasi atribut buku secara utuh (*Full Replace*). | `200 OK`, `401`, `404`, `422` |
| **PATCH**| `/api/v1/bukus/{id}` | Bearer Token | Memperbarui sebagian atribut buku (*Partial Update*, misal ubah stok). | `200 OK`, `400 Bad Request`, `401`, `404` |
| **DELETE**| `/api/v1/bukus/{id}` | Bearer Token | Menghapus buku dari katalog (Ditolak jika masih ada peminjaman aktif). | `204 No Content`, `401`, `404`, `409 Conflict` |
| **GET** | `/api/v1/bukus/{id}/referensi-eksternal` | Publik | **REST Consumer**: Mengambil pengayaan metadata buku dari OpenLibrary REST API via `Http` facade. | `200 OK`, `404`, `502 Bad Gateway` |
| **GET** | `/api/v1/peminjamans` | Bearer Token | Menampilkan riwayat transaksi sirkulasi peminjaman terpaginasi (filter `?status=` & `?nim=`). | `200 OK`, `401 Unauthorized` |
| **POST** | `/api/v1/peminjamans` | Bearer Token | Mencatat peminjaman buku baru secara atomik (ACID + LockForUpdate + Dekremen Stok). | `201 Created` + `Location`, `409`, `422` |
| **PATCH**| `/api/v1/peminjamans/{id}/kembalikan`| Bearer Token | Memproses pengembalian buku secara atomik (ACID + LockForUpdate + Inkremen Stok). | `200 OK`, `404`, `409 Conflict` |
| **GET** | `/api/v1/portfolios/github/{username}` | Publik | **REST Consumer**: Mengambil portofolio dan 3 repositori terbaru pengguna GitHub API. | `200 OK`, `404`, `502 Bad Gateway` |

---

## V. Panduan Instalasi & Pengujian Cepat

### 5.1 Prasyarat Perangkat Lunak
- **PHP**: Versi 8.2 atau 8.3+ (Ekstensi `pdo_sqlite`, `curl`, `json`, `xml`, `mbstring`, `openssl` aktif)
- **Composer**: Versi 2.6+
- **Database**: SQLite (Bawaan) atau MySQL

### 5.2 Langkah Setup Cepat
Buka terminal pada direktori proyek:
```bash
# 1. Pindah ke branch pengerjaan REST API
git checkout week9-rest-api

# 2. Pasang dependensi PHP
composer install

# 3. Konfigurasi berkas environment
copy .env.example .env
php artisan key:generate

# 4. Migrasi skema basis data & jalankan seeder
php artisan migrate:fresh --seed

# 5. Jalankan server lokal
php artisan serve
```

### 5.3 Kredensial Akun Pengujian API
- **Email Pustakawan**: `pustakawan@del.ac.id`
- **Kata Sandi**: `password123`

### 5.4 Cara Import Postman Collection & Environment
1. Buka aplikasi **Postman Desktop**.
2. Klik tombol **Import** di sudut kiri atas.
3. Pilih berkas koleksi: `docs/SIPUS-Del-API-v1.postman_collection.json`.
4. Seluruh 23 skenario pengujian telah dilengkapi otomatis dengan variabel koleksi:
   - `{{base_url}}`: `http://localhost:8000/api/v1`
   - `{{soap_url}}`: `http://localhost:8000/soap/sipus`
   - `{{token}}`: Ditetapkan secara otomatis oleh test script pada request *05. POST Login Pustakawan Sukses*!

---

## VI. Galeri Bukti Pengujian Komprehensif (7 Skenario Wajib)

### Skenario 1: Uji Layanan SOAP Bebas Pustaka XML & SOAP Fault
#### A. Pengecekan Sukses (Bebas Pustaka - NIM `12S22001`, 0 Pinjaman Aktif)
**Request**:
```http
POST /soap/sipus HTTP/1.1
Host: localhost:8000
Content-Type: text/xml; charset=utf-8

<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S22001</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
```
**Response (HTTP/1.1 200 OK)**:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Header>
        <m:ProviderInfo>SIPUS-Del Legacy SOAP Clearance Engine v1.0</m:ProviderInfo>
    </soap:Header>
    <soap:Body>
        <m:CheckClearanceStatusResponse>
            <m:nim_peminjam>12S22001</m:nim_peminjam>
            <m:nama_peminjam>Bona Pandiangan</m:nama_peminjam>
            <m:active_loans>0</m:active_loans>
            <m:is_cleared>true</m:is_cleared>
            <m:status_keterangan>Bebas Pustaka Terverifikasi (Tidak ada tanggungan peminjaman buku aktif).</m:status_keterangan>
        </m:CheckClearanceStatusResponse>
    </soap:Body>
</soap:Envelope>
```

#### B. Pengecekan Negatif (SOAP Fault - XML Malformed)
**Request**:
```http
POST /soap/sipus HTTP/1.1
Host: localhost:8000
Content-Type: text/xml; charset=utf-8

<invalid-malformed-xml-envelope>
```
**Response (HTTP/1.1 500 Internal Server Error)**:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <soap:Fault>
            <faultcode>soap:Client</faultcode>
            <faultstring>Format XML tidak valid (Malformed XML).</faultstring>
        </soap:Fault>
    </soap:Body>
</soap:Envelope>
```

---

### Skenario 2: Login Stateless Sanctum Bearer Token & Profil Pengguna
**Request**:
```http
POST /api/v1/auth/login HTTP/1.1
Host: localhost:8000
Accept: application/json
Content-Type: application/json

{
    "email": "pustakawan@del.ac.id",
    "password": "password123",
    "device_name": "postman-desktop"
}
```
**Response (HTTP/1.1 200 OK)**:
```json
{
    "success": true,
    "message": "Otentikasi API berhasil. Lampirkan access_token pada header Authorization: Bearer <token>.",
    "data": {
        "token_type": "Bearer",
        "access_token": "1|2B6kRxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "user": {
            "id": 1,
            "name": "Pustakawan SIPUS-Del",
            "email": "pustakawan@del.ac.id"
        }
    }
}
```

---

### Skenario 3: GET Katalog Terpaginasi & Kontrol Hypermedia HATEOAS
**Request**:
```http
GET /api/v1/bukus?per_page=2&sort=stok&dir=desc HTTP/1.1
Host: localhost:8000
Accept: application/json
```
**Response (HTTP/1.1 200 OK)**:
```json
{
    "success": true,
    "message": "Daftar koleksi buku perpustakaan berhasil diambil.",
    "data": [
        {
            "id": 3,
            "isbn": "9786022987103",
            "judul": "Arsitektur Perangkat Lunak Modern: Dari Monolit ke Microservices",
            "penulis": "Chandro Pardede, S.Kom., M.Sc.",
            "penerbit": "Del Press Laguboti",
            "tahun_terbit": 2024,
            "kategori_id": 1,
            "kategori": {
                "id": 1,
                "kode_kategori": "RPL",
                "nama_kategori": "Rekayasa Perangkat Lunak"
            },
            "stok": 15,
            "sinopsis": "Kajian komprehensif transformasi arsitektur perangkat lunak dari pola MVC terpadu menuju service-oriented...",
            "total_peminjaman": 0,
            "status_ketersediaan": "TERSEDIA",
            "riwayat_peminjaman": [],
            "created_at": "2026-10-13T06:45:00+00:00",
            "updated_at": "2026-10-13T06:45:00+00:00",
            "_links": {
                "self": {
                    "href": "http://localhost:8000/api/v1/bukus/3",
                    "method": "GET"
                },
                "kategori": {
                    "href": "http://localhost:8000/api/v1/kategoris/1",
                    "method": "GET"
                },
                "referensi_eksternal": {
                    "href": "http://localhost:8000/api/v1/bukus/3/referensi-eksternal",
                    "method": "GET"
                },
                "update_full": {
                    "href": "http://localhost:8000/api/v1/bukus/3",
                    "method": "PUT"
                },
                "update_partial": {
                    "href": "http://localhost:8000/api/v1/bukus/3",
                    "method": "PATCH"
                },
                "delete": {
                    "href": "http://localhost:8000/api/v1/bukus/3",
                    "method": "DELETE"
                },
                "pinjam_buku": {
                    "href": "http://localhost:8000/api/v1/peminjamans",
                    "method": "POST"
                }
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 2,
        "total_items": 20,
        "total_pages": 10,
        "links": {
            "first": "http://localhost:8000/api/v1/bukus?page=1",
            "prev": null,
            "next": "http://localhost:8000/api/v1/bukus?page=2",
            "last": "http://localhost:8000/api/v1/bukus?page=10"
        }
    }
}
```

---

### Skenario 4: POST Penambahan Buku Baru (201 Created + Header Location)
**Request**:
```http
POST /api/v1/bukus HTTP/1.1
Host: localhost:8000
Accept: application/json
Authorization: Bearer 1|2B6kRxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Content-Type: application/json

{
    "isbn": "9786020000011",
    "judul": "Arsitektur Cloud & RESTful Web Services Modern",
    "penulis": "Chandro Pardede, S.Kom., M.Sc.",
    "penerbit": "Del Press Laguboti",
    "tahun_terbit": 2025,
    "kategori_id": 1,
    "stok": 10,
    "sinopsis": "Buku komprehensif panduan membangun layanan terdistribusi berskala besar."
}
```
**Response Headers**:
```http
HTTP/1.1 201 Created
Content-Type: application/json
Location: http://localhost:8000/api/v1/bukus/21
```
**Response Body**:
```json
{
    "success": true,
    "message": "Buku baru berhasil ditambahkan ke katalog.",
    "data": {
        "id": 21,
        "isbn": "9786020000011",
        "judul": "Arsitektur Cloud & RESTful Web Services Modern",
        "stok": 10,
        "status_ketersediaan": "TERSEDIA",
        "_links": {
            "self": {
                "href": "http://localhost:8000/api/v1/bukus/21",
                "method": "GET"
            },
            "pinjam_buku": {
                "href": "http://localhost:8000/api/v1/peminjamans",
                "method": "POST"
            }
        }
    }
}
```

---

### Skenario 5: Bukti Nyata Perbedaan Semantik PUT vs PATCH
#### A. PATCH `/api/v1/bukus/1` (Hanya Mengubah Stok Menjadi 25)
**Request**:
```http
PATCH /api/v1/bukus/1 HTTP/1.1
Host: localhost:8000
Accept: application/json
Authorization: Bearer 1|2B6kRxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Content-Type: application/json

{
    "stok": 25
}
```
**Hasil**: `stok` berubah menjadi `25`, sedangkan atribut `judul`, `penulis`, `penerbit`, dan `sinopsis` **tetap utuh dan tidak terhapus**!

#### B. PUT `/api/v1/bukus/1` (Full Replacement Tanpa Kolom Sinopsis)
**Request**:
```http
PUT /api/v1/bukus/1 HTTP/1.1
Host: localhost:8000
Accept: application/json
Authorization: Bearer 1|2B6kRxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Content-Type: application/json

{
    "isbn": "9786022987101",
    "judul": "Clean Code Edisi Bahasa Indonesia Komprehensif",
    "penulis": "Robert C. Martin (Tim Penerjemah Del)",
    "penerbit": "Informatika Bandung",
    "tahun_terbit": 2024,
    "kategori_id": 1,
    "stok": 15
}
```
**Hasil**: Seluruh atribut representasi diganti secara utuh, dan karena kolom nullable `sinopsis` tidak dikirim pada body PUT murni, nilainya direset menjadi `null` di basis data.

---

### Skenario 6: Transaksi Sirkulasi ACID, Penolakan Stok Habis (422) & Duplikasi (409)
#### A. Peminjaman Berhasil (Stok Berkurang Secara Atomik)
**Request**:
```http
POST /api/v1/peminjamans HTTP/1.1
Host: localhost:8000
Accept: application/json
Authorization: Bearer 1|2B6kRxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Content-Type: application/json

{
    "buku_id": 2,
    "nim_peminjam": "12S24050",
    "nama_peminjam": "Daniel Situmorang",
    "durasi_hari": 7
}
```
**Response (HTTP/1.1 201 Created)**:
```json
{
    "success": true,
    "message": "Peminjaman buku 'Pola Desain Gang of Four: Konsep dan Implementasi Berorientasi Objek' berhasil dicatat. Sisa stok kini: 7.",
    "data": {
        "id": 3,
        "buku_id": 2,
        "nim_peminjam": "12S24050",
        "status": "dipinjam",
        "_links": {
            "self": { "href": "http://localhost:8000/api/v1/peminjamans/3", "method": "GET" },
            "kembalikan_buku": { "href": "http://localhost:8000/api/v1/peminjamans/3/kembalikan", "method": "PATCH" }
        }
    }
}
```

#### B. Penolakan Duplikasi Pinjaman (HTTP 409 Conflict)
Ketika mahasiswa yang sama (`12S24050`) mencoba meminjam kembali buku yang sama sebelum dikembalikan:
```json
{
    "type": "http://localhost:8000/docs/errors/duplicate-active-loan",
    "title": "Duplicate Active Loan Conflict",
    "status": 409,
    "detail": "Mahasiswa dengan NIM '12S24050' masih meminjam buku 'Pola Desain Gang of Four: Konsep dan Implementasi Berorientasi Objek' dan belum mengembalikannya.",
    "instance": "/api/v1/peminjamans"
}
```

#### C. Penolakan Stok Habis (HTTP 422 Unprocessable Content)
Ketika stok buku mencapai $0$:
```json
{
    "type": "http://localhost:8000/docs/errors/stock-exhausted",
    "title": "Book Stock Exhausted",
    "status": 422,
    "detail": "Maaf, stok fisik buku 'Buku Habis Stok' sedang habis (0 tersisa).",
    "instance": "/api/v1/peminjamans"
}
```

---

### Skenario 7: Latihan REST API Consumer (GitHub API & OpenLibrary API)
#### A. REST Consumer: Portofolio GitHub Pengguna (`octocat`)
**Request**:
```http
GET /api/v1/portfolios/github/octocat HTTP/1.1
Host: localhost:8000
Accept: application/json
```
**Response (HTTP/1.1 200 OK)**:
```json
{
    "success": true,
    "message": "Data portofolio koding mahasiswa berhasil diambil dari GitHub API.",
    "data": {
        "github_login": "octocat",
        "display_name": "The Octocat",
        "bio": null,
        "public_repos": 8,
        "followers": 22964,
        "profile_url": "https://github.com/octocat",
        "latest_repos": [
            {
                "repo_name": "Spoon-Knife",
                "language": "HTML",
                "stars": 14120,
                "html_url": "https://github.com/octocat/Spoon-Knife"
            }
        ]
    }
}
```

#### B. REST Consumer: Pengayaan Metadata Buku via OpenLibrary REST API
**Request**:
```http
GET /api/v1/bukus/1/referensi-eksternal HTTP/1.1
Host: localhost:8000
Accept: application/json
```
**Response (HTTP/1.1 200 OK)**:
```json
{
    "success": true,
    "message": "Metadata referensi buku eksternal berhasil diambil dari OpenLibrary API.",
    "data": {
        "buku_lokal": {
            "id": 1,
            "isbn": "9786022987101",
            "judul": "Clean Code: Panduan Praktis Pengembangan Perangkat Lunak Tangkas"
        },
        "external_source": "OpenLibrary REST API",
        "ditemukan": true,
        "metadata": {
            "title": "Clean Code: A Handbook of Agile Software Craftsmanship",
            "authors": [
                "Robert C. Martin"
            ],
            "publishers": [
                "Prentice Hall"
            ],
            "publish_date": "August 1, 2008",
            "number_of_pages": 464,
            "preview_url": "https://openlibrary.org/books/OL11369796M/Clean_Code",
            "cover_url": "https://covers.openlibrary.org/b/id/8091016-L.jpg"
        }
    }
}
```

---

## VII. Ringkasan Eksekusi Pengujian Otomatis (PHPUnit Test Suite)

Seluruh fungsionalitas diuji menggunakan test suite otomatis (`php artisan test`) yang menghasilkan tingkat kelulusan 100%:
- `Tests\Unit\ExampleTest`: 1 passed
- `Tests\Feature\ApiCoreTest`: 2 passed (RFC 7807 404 & 401 unauthenticated check)
- `Tests\Feature\AuthApiTest`: 3 passed (Login 200, Login 401, Bearer token flow & revocation)
- `Tests\Feature\BukuApiTest`: 8 passed (Katalog, paginasi, HATEOAS, 201 + Location, PUT vs PATCH, delete conflict 409, delete 204)
- `Tests\Feature\BukuTest`: 9 passed (Antarmuka Web Blade Minggu 5-6 tetap terjaga utuh)
- `Tests\Feature\ConsumerApiTest`: 3 passed (OpenLibrary lookup, fallback, GitHub portfolio)
- `Tests\Feature\KategoriTest`: 7 passed (Antarmuka Web Kategori Blade Minggu 5-6 tetap terjaga utuh)
- `Tests\Feature\PeminjamanApiTest`: 6 passed (Sirkulasi terpaginasi, peminjaman ACID, penolakan stok habis 422, duplikasi 409, pengembalian 200 + stok inkremen)
- `Tests\Feature\SoapClearanceTest`: 4 passed (Kontrak WSDL, SOAP clearance sukses, pinjaman aktif, SOAP fault)

**Total Hasil**: **44 passed (216 assertions)** &bull; Status: **HIJAU (100% Lulus)**.

---

## VIII. Kepatuhan Standar Git & Keamanan Repositori

1. **Branching**: Seluruh pembaruan diimplementasikan pada branch baru `week9-rest-api`.
2. **Conventional Commits**: Riwayat commit disusun bertahap, bersih, dan sesuai urutan modul:
   - `feat(soap): add concise LibrarySoapController and WSDL contract for clearance check`
   - `feat(api-core): install laravel api routing, ApiResponse trait, and RFC 7807 exception handler`
   - `feat(api-auth): implement stateless Sanctum bearer token login and revocation`
   - `feat(api-books): build BukuApiController and BukuResource with dynamic HATEOAS links`
   - `feat(api-loans): implement ACID book borrowing and return REST endpoints with lockForUpdate`
   - `feat(api-consumer): integrate external book metadata lookup via Laravel Http client`
   - `docs(contract): add OpenAPI 3.0 specification, Postman collection, and test report in README`
3. **Disiplin Keamanan Token**: Berkas `.env` tidak di-commit ke Git. Seluruh token rahasia disensor pada teks dokumentasi sesuai pedoman *Secret Scanning*.
