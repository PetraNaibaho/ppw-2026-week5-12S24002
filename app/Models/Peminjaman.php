<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'peminjamans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'buku_id',
        'nim_peminjam',
        'nama_peminjam',
        'tanggal_pinjam',
        'durasi_hari',
        'tanggal_kembali',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'buku_id' => 'integer',
        'durasi_hari' => 'integer',
        'tanggal_pinjam' => 'date',
        'tanggal_kembali' => 'date',
    ];

    /**
     * Relasi Many-to-One: Peminjaman merujuk ke satu Buku.
     */
    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }
}
