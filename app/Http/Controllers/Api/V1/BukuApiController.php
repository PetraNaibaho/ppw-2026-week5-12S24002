<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BukuApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus
     * Mengambil koleksi buku terpaginasi (Filter, Search, Sort, Paginasi)
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Batasi parameter pengurutan pada kolom yang sah (mencegah SQL Injection pada ORDER BY)
        $allowedSorts = ['judul', 'penulis', 'penerbit', 'tahun_terbit', 'stok', 'created_at'];
        $sortBy = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'judul';
        $sortDir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        // 2. Susun kueri Eloquent bebas N+1 problem
        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->query('kategori_id'), fn ($q, $catId) => $q->where('kategori_id', $catId))
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('stok', '>', 0))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        // 3. Kembalikan koleksi terbungkus BukuResource beserta metadata paginasi
        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar koleksi buku perpustakaan berhasil diambil.',
            status: 200,
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/bukus/{id}
     * Mengambil detail satu buku berdasarkan ID atau ISBN
     */
    public function show(string $identifier): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])
            ->withCount('peminjamans')
            ->where('id', $identifier)
            ->orWhere('isbn', $identifier)
            ->first();

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan pengenal '{$identifier}' tidak ditemukan di katalog basis data.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/bukus
     * Menambah buku baru ke katalog -> Wajib mereturn 201 Created + Header Location
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'isbn' => 'required|string|max:13|unique:bukus,isbn',
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0|max:1000',
            'sinopsis' => 'nullable|string|max:2000',
        ]);

        $buku = Buku::create($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Buku baru berhasil ditambahkan ke katalog.'
        );
    }

    /**
     * PUT / PATCH /api/v1/bukus/{id}
     * Mendemonstrasikan perbedaan semantik PUT (Full Replace) vs PATCH (Partial Update)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan di katalog.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            // SEMANTIK PUT: Seluruh atribut representasi WAJIB dikirim secara utuh!
            $validated = $request->validate([
                'isbn' => "required|string|max:13|unique:bukus,isbn,{$buku->id}",
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:150',
                'penerbit' => 'required|string|max:150',
                'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'kategori_id' => 'required|exists:kategoris,id',
                'stok' => 'required|integer|min:0|max:1000',
                'sinopsis' => 'nullable|string|max:2000',
            ]);

            // Pada PUT murni, jika field nullable tidak disertakan, nilainya direset ke null
            $validated['sinopsis'] = $request->input('sinopsis', null);
        } else {
            // SEMANTIK PATCH: HANYA memvalidasi & memodifikasi atribut yang dikirimkan di body!
            $validated = $request->validate([
                'isbn' => "sometimes|required|string|max:13|unique:bukus,isbn,{$buku->id}",
                'judul' => 'sometimes|required|string|max:255',
                'penulis' => 'sometimes|required|string|max:150',
                'penerbit' => 'sometimes|required|string|max:150',
                'tahun_terbit' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
                'kategori_id' => 'sometimes|required|exists:kategoris,id',
                'stok' => 'sometimes|required|integer|min:0|max:1000',
                'sinopsis' => 'sometimes|nullable|string|max:2000',
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }
        }

        $buku->update($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi buku berhasil diganti (PUT).'
                : 'Atribut buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    /**
     * DELETE /api/v1/bukus/{id}
     * Menghapus buku -> 409 Conflict jika sedang dipinjam aktif, 204 No Content jika sukses
     */
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::withCount(['peminjamans' => function ($q) {
            $q->where('status', 'dipinjam');
        }])->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan di basis data.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        // Aturan Bisnis: Tolak penghapusan buku jika sedang memiliki peminjaman aktif!
        if ($buku->peminjamans_count > 0) {
            return $this->problemResponse(
                title: 'Conflict — Book Has Active Loans',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena masih tercatat dipinjam pada {$buku->peminjamans_count} transaksi aktif.",
                status: 409,
                errorSlug: 'book-has-active-loans'
            );
        }

        $buku->delete();

        return $this->noContentResponse();
    }
}
