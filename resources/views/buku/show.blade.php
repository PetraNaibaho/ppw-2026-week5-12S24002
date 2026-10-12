<x-layout>
    <x-slot:title>{{ $buku->judul }}</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('buku.index') }}" class="text-decoration-none text-muted">Katalog Buku</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('kategori.show', $buku->kategori) }}" class="text-decoration-none text-muted">{{ $buku->kategori->nama_kategori }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($buku->judul, 30) }}</li>
                </ol>
            </nav>

            <div class="card card-custom shadow-sm overflow-hidden mb-4">
                <div class="card-header card-header-del py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-book-half fs-5"></i>
                        <span class="fw-semibold">Detail Informasi Buku</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                        <a href="{{ route('buku.edit', $buku) }}" class="btn btn-warning btn-sm fw-semibold text-dark">
                            <i class="bi bi-pencil-square me-1"></i> Ubah Data
                        </a>
                        <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku \'{{ addslashes($buku->judul) }}\'?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge badge-del px-2 py-1">
                                    <i class="bi bi-tag-fill me-1"></i>{{ $buku->kategori->nama_kategori }}
                                </span>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                    ISBN: {{ $buku->isbn }}
                                </span>
                            </div>

                            <h3 class="fw-bold text-dark mb-3">{{ $buku->judul }}</h3>

                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted fw-bold small" style="letter-spacing: 0.05em;">Sinopsis & Deskripsi</h6>
                                <div class="p-3 bg-light rounded-3 text-secondary lh-base border">
                                    {{ $buku->sinopsis ?: 'Tidak ada sinopsis atau keterangan ringkas yang disertakan untuk buku ini.' }}
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="border rounded p-3 bg-white">
                                        <div class="text-muted small">Penulis</div>
                                        <div class="fw-semibold text-dark fs-6">{{ $buku->penulis }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border rounded p-3 bg-white">
                                        <div class="text-muted small">Penerbit</div>
                                        <div class="fw-semibold text-dark fs-6">{{ $buku->penerbit }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border rounded p-3 bg-white">
                                        <div class="text-muted small">Tahun Terbit</div>
                                        <div class="fw-semibold text-dark fs-6">{{ $buku->tahun_terbit }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border rounded p-3 bg-white">
                                        <div class="text-muted small">Waktu Registrasi</div>
                                        <div class="fw-semibold text-dark fs-6">{{ $buku->created_at->format('d M Y, H:i') }} WIB</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Stock Status Card -->
                            <div class="card bg-light border-0 p-3 rounded-3 mb-3 text-center">
                                <div class="text-muted small mb-1 fw-medium">Status Ketersediaan Sirkulasi</div>
                                <div class="my-2">
                                    @if($buku->stok > 10)
                                        <div class="display-6 fw-bold text-success">{{ $buku->stok }}</div>
                                        <span class="badge bg-success-subtle text-success border border-success px-3 py-1 mt-1">Stok Tersedia Banyak</span>
                                    @elseif($buku->stok > 0)
                                        <div class="display-6 fw-bold text-warning">{{ $buku->stok }}</div>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-3 py-1 mt-1">Stok Menipis</span>
                                    @else
                                        <div class="display-6 fw-bold text-danger">0</div>
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-1 mt-1">Stok Habis</span>
                                    @endif
                                </div>
                                <p class="small text-muted mt-2 mb-0">Total eksemplar fisik yang terdaftar pada rak perpustakaan.</p>
                            </div>

                            <!-- Category Quick Info -->
                            <div class="card border rounded-3 p-3">
                                <h6 class="fw-semibold text-dark mb-2">
                                    <i class="bi bi-info-circle text-primary me-1" style="color: #4C1D95 !important;"></i> Informasi Kategori
                                </h6>
                                <p class="small text-muted mb-2">Buku ini diklasifikasikan di bawah klasifikasi akademik:</p>
                                <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border small">
                                    <span class="fw-medium text-dark">{{ $buku->kategori->nama_kategori }}</span>
                                    <span class="badge bg-secondary font-monospace">{{ $buku->kategori->kode_kategori }}</span>
                                </div>
                                <a href="{{ route('kategori.show', $buku->kategori) }}" class="btn btn-outline-del btn-sm mt-3 w-100">
                                    <i class="bi bi-folder2-open me-1"></i> Lihat Koleksi Terkait
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
