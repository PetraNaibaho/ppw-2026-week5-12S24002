<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BookReferenceController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus/{id}/referensi-eksternal
     * REST Service Consumer: Mengambil metadata pengayaan buku dari OpenLibrary REST API
     */
    public function show(string $id): JsonResponse
    {
        $buku = Buku::where('id', $id)->orWhere('isbn', $id)->first();

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan pengenal '{$id}' tidak ditemukan di katalog lokal.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $buku->isbn);
        $bibKey = "ISBN:{$cleanIsbn}";

        try {
            // Panggil OpenLibrary REST API dengan batas waktu 8 detik (Http Facade)
            $response = Http::timeout(8)
                ->acceptJson()
                ->get('https://openlibrary.org/api/books', [
                    'bibkeys' => $bibKey,
                    'format' => 'json',
                    'jscmd' => 'data',
                ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal berkomunikasi dengan layanan eksternal OpenLibrary API (koneksi timeout atau jaringan offline).',
                'fallback_data' => [
                    'id' => $buku->id,
                    'isbn' => $buku->isbn,
                    'judul' => $buku->judul,
                    'penulis' => $buku->penulis,
                ],
            ], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan eksternal OpenLibrary API sedang mengalami gangguan (Upstream Error).',
            ], 502);
        }

        $data = $response->json();

        if (!isset($data[$bibKey])) {
            return $this->successResponse([
                'buku_lokal' => [
                    'id' => $buku->id,
                    'isbn' => $buku->isbn,
                    'judul' => $buku->judul,
                    'penulis' => $buku->penulis,
                    'penerbit' => $buku->penerbit,
                    'tahun_terbit' => $buku->tahun_terbit,
                ],
                'external_source' => 'OpenLibrary REST API',
                'ditemukan' => false,
                'metadata' => null,
            ], 'Buku tidak ditemukan pada direktori OpenLibrary eksternal, menampilkan metadata lokal.');
        }

        $item = $data[$bibKey];
        $metadata = [
            'title' => $item['title'] ?? $buku->judul,
            'authors' => isset($item['authors']) ? collect($item['authors'])->pluck('name')->toArray() : [$buku->penulis],
            'publishers' => isset($item['publishers']) ? collect($item['publishers'])->pluck('name')->toArray() : [$buku->penerbit],
            'publish_date' => $item['publish_date'] ?? null,
            'number_of_pages' => $item['number_of_pages'] ?? null,
            'preview_url' => $item['url'] ?? "https://openlibrary.org/isbn/{$cleanIsbn}",
            'cover_url' => $item['cover']['large'] ?? $item['cover']['medium'] ?? $item['cover']['small'] ?? null,
        ];

        return $this->successResponse([
            'buku_lokal' => [
                'id' => $buku->id,
                'isbn' => $buku->isbn,
                'judul' => $buku->judul,
            ],
            'external_source' => 'OpenLibrary REST API',
            'ditemukan' => true,
            'metadata' => $metadata,
        ], 'Metadata referensi buku eksternal berhasil diambil dari OpenLibrary API.');
    }
}
