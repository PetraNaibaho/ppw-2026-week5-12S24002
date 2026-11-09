<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Hitung jumlah transaksi peminjaman jika withCount('peminjamans') dipanggil
        $totalPeminjaman = $this->whenCounted('peminjamans', fn () => $this->peminjamans_count, 0);
        $isFull = $this->stok <= 0;

        // Bangun kontrol navigasi Hypermedia HATEOAS (Richardson Maturity Model Level 3)
        $links = [
            'self' => [
                'href' => url("/api/v1/bukus/{$this->id}"),
                'method' => 'GET',
            ],
            'kategori' => [
                'href' => url("/api/v1/kategoris/{$this->kategori_id}"),
                'method' => 'GET',
            ],
            'referensi_eksternal' => [
                'href' => url("/api/v1/bukus/{$this->id}/referensi-eksternal"),
                'method' => 'GET',
            ],
            'update_full' => [
                'href' => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PUT',
            ],
            'update_partial' => [
                'href' => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PATCH',
            ],
            'delete' => [
                'href' => url("/api/v1/bukus/{$this->id}"),
                'method' => 'DELETE',
            ],
        ];

        // Keindahan HATEOAS: Link aksi 'pinjam_buku' HANYA muncul jika stok > 0!
        if (!$isFull) {
            $links['pinjam_buku'] = [
                'href' => url('/api/v1/peminjamans'),
                'method' => 'POST',
            ];
        }

        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'judul' => $this->judul,
            'penulis' => $this->penulis,
            'penerbit' => $this->penerbit,
            'tahun_terbit' => (int) $this->tahun_terbit,
            'kategori_id' => $this->kategori_id,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'stok' => (int) $this->stok,
            'sinopsis' => $this->sinopsis,
            'total_peminjaman' => $totalPeminjaman,
            'status_ketersediaan' => $isFull ? 'HABIS' : 'TERSEDIA',
            'riwayat_peminjaman' => PeminjamanResource::collection($this->whenLoaded('peminjamans')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            '_links' => $links,
        ];
    }
}
