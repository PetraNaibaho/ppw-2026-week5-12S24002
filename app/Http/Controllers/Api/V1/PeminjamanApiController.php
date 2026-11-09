<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans
     * Menampilkan daftar transaksi peminjaman terpaginasi (Filter status, nim, & buku_id)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('nim'), fn ($q, $nim) => $q->where('nim_peminjam', $nim))
            ->when($request->query('buku_id'), fn ($q, $bukuId) => $q->where('buku_id', $bukuId))
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar riwayat transaksi sirkulasi peminjaman buku berhasil diambil.',
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
     * GET /api/v1/peminjamans/{id}
     * Menampilkan detail satu transaksi peminjaman
     */
    public function show(string $id): JsonResponse
    {
        $peminjaman = Peminjaman::with('buku')->find($id);

        if (!$peminjaman) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Data transaksi peminjaman dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'loan-not-found'
            );
        }

        return $this->successResponse(
            data: new PeminjamanResource($peminjaman),
            message: 'Detail transaksi peminjaman berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/peminjamans
     * Mencatat transaksi peminjaman buku baru (ACID Transaction + Pessimistic Locking)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|exists:bukus,id',
            'nim_peminjam' => 'required|string|max:20',
            'nama_peminjam' => 'required|string|max:150',
            'durasi_hari' => 'nullable|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Kunci baris buku secara eksklusif (SELECT ... FOR UPDATE) untuk menangkal Race Condition
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            if (!$buku) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: 'Buku yang akan dipinjam tidak ditemukan di katalog.',
                    status: 404,
                    errorSlug: 'book-not-found'
                );
            }

            // 2. Cek ketersediaan sisa stok fisik buku -> 422 Unprocessable Content jika stok habis
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Book Stock Exhausted',
                    detail: "Maaf, stok fisik buku '{$buku->judul}' sedang habis (0 tersisa).",
                    status: 422,
                    errorSlug: 'stock-exhausted'
                );
            }

            // 3. Cek apakah mahasiswa masih meminjam buku yang sama dan belum dikembalikan -> 409 Conflict
            $nim = strtoupper(trim($validated['nim_peminjam']));
            $alreadyBorrowing = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $nim)
                ->where('status', 'dipinjam')
                ->exists();

            if ($alreadyBorrowing) {
                return $this->problemResponse(
                    title: 'Duplicate Active Loan Conflict',
                    detail: "Mahasiswa dengan NIM '{$nim}' masih meminjam buku '{$buku->judul}' dan belum mengembalikannya.",
                    status: 409,
                    errorSlug: 'duplicate-active-loan'
                );
            }

            // 4. Kurangi stok buku (-1) dan catat transaksi peminjaman secara atomik
            $buku->decrement('stok');

            $peminjaman = Peminjaman::create([
                'buku_id' => $buku->id,
                'nim_peminjam' => $nim,
                'nama_peminjam' => trim($validated['nama_peminjam']),
                'tanggal_pinjam' => now()->toDateString(),
                'durasi_hari' => $validated['durasi_hari'] ?? 7,
                'status' => 'dipinjam',
            ]);

            $peminjaman->load('buku');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman buku '{$buku->judul}' berhasil dicatat. Sisa stok kini: {$buku->fresh()->stok}."
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan
     * Memproses pengembalian buku di dalam DB::transaction() + lockForUpdate()
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::find($id);

            if (!$peminjaman) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Data transaksi peminjaman dengan ID '{$id}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'loan-not-found'
                );
            }

            // Tolak jika sudah dikembalikan sebelumnya -> 409 Conflict
            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Conflict — Book Already Returned',
                    detail: "Buku pada transaksi ini sudah pernah dikembalikan sebelumnya (status: dikembalikan).",
                    status: 409,
                    errorSlug: 'loan-already-returned'
                );
            }

            // Kunci baris buku & tambahkan stok (+1)
            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            if ($buku) {
                $buku->increment('stok');
            }

            $peminjaman->load('buku');

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman),
                message: "Buku '{$buku?->judul}' berhasil dikembalikan. Stok buku kini bertambah menjadi {$buku?->fresh()->stok}."
            );
        });
    }
}
