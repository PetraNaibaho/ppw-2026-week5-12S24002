<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isDipinjam = ($this->status === 'dipinjam');

        $links = [
            'self' => [
                'href' => url("/api/v1/peminjamans/{$this->id}"),
                'method' => 'GET',
            ],
            'buku' => [
                'href' => url("/api/v1/bukus/{$this->buku_id}"),
                'method' => 'GET',
            ],
        ];

        // Keindahan HATEOAS: Link aksi 'kembalikan_buku' HANYA muncul jika status === 'dipinjam'!
        if ($isDipinjam) {
            $links['kembalikan_buku'] = [
                'href' => url("/api/v1/peminjamans/{$this->id}/kembalikan"),
                'method' => 'PATCH',
            ];
        }

        return [
            'id' => $this->id,
            'buku_id' => $this->buku_id,
            'buku' => $this->relationLoaded('buku') && $this->buku ? [
                'id' => $this->buku->id,
                'isbn' => $this->buku->isbn,
                'judul' => $this->buku->judul,
            ] : null,
            'nim_peminjam' => $this->nim_peminjam,
            'nama_peminjam' => $this->nama_peminjam,
            'tanggal_pinjam' => $this->tanggal_pinjam instanceof \Carbon\CarbonInterface
                ? $this->tanggal_pinjam->format('Y-m-d')
                : (string) $this->tanggal_pinjam,
            'durasi_hari' => (int) $this->durasi_hari,
            'tanggal_kembali' => $this->tanggal_kembali instanceof \Carbon\CarbonInterface
                ? $this->tanggal_kembali->format('Y-m-d')
                : ($this->tanggal_kembali ? (string) $this->tanggal_kembali : null),
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            '_links' => $links,
        ];
    }
}
