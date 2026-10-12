<x-layout>
    <x-slot:title>Daftar Kategori Buku</x-slot:title>

    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-tags-fill text-primary" style="color: #4C1D95 !important;"></i>
                Master Kategori Koleksi
            </h2>
            <p class="text-muted mb-0 small">
                Pengelolaan klasifikasi taksonomi dan rumpun ilmu koleksi perpustakaan Del.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-book me-1"></i> Kembali ke Katalog Buku
            </a>
            <a href="{{ route('kategori.create') }}" class="btn btn-del btn-sm shadow-sm">
                <i class="bi bi-folder-plus me-1"></i> Tambah Kategori Baru
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card card-custom mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('kategori.index') }}" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari kode kategori atau nama klasifikasi..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-del btn-sm flex-fill">
                        <i class="bi bi-filter me-1"></i> Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset pencarian">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table of Categories -->
    <div class="card card-custom shadow-sm">
        <div class="card-header card-header-del d-flex justify-content-between align-items-center py-3">
            <span class="fw-semibold">
                <i class="bi bi-list-check me-1"></i> Daftar Klasifikasi Kategori
            </span>
            <span class="badge bg-secondary-subtle text-secondary small">
                Total {{ $kategoris->total() }} kategori terdaftar
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">No</th>
                        <th style="width: 150px;">Kode Kategori</th>
                        <th>Nama Klasifikasi Kategori</th>
                        <th class="text-center" style="width: 140px;">Jumlah Buku</th>
                        <th class="text-center" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoris as $kategori)
                        <tr>
                            <td class="text-center text-muted fw-medium small">
                                {{ $kategoris->firstItem() ? $kategoris->firstItem() + $loop->index : $loop->iteration }}
                            </td>
                            <td>
                                <span class="badge bg-dark font-monospace px-2 py-1">
                                    {{ $kategori->kode_kategori }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <a href="{{ route('kategori.show', $kategori) }}" class="text-decoration-none text-dark hover-purple">
                                        {{ $kategori->nama_kategori }}
                                    </a>
                                </div>
                                <div class="text-muted small">
                                    Dibuat pada: {{ $kategori->created_at->format('d M Y') }}
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('buku.index', ['kategori_id' => $kategori->id]) }}" class="badge badge-del text-decoration-none px-3 py-1 fs-7" title="Lihat koleksi buku di kategori ini">
                                    <i class="bi bi-book me-1"></i> {{ $kategori->bukus_count }} judul
                                </a>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('kategori.show', $kategori) }}" class="btn btn-outline-primary" title="Lihat Buku Terkait">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-outline-warning" title="Ubah Kategori">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Menghapus kategori \'{{ addslashes($kategori->nama_kategori) }}\' juga akan menghapus seluruh buku yang berelasi dengannya! Lanjutkan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Kategori">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-tags fs-1 text-secondary opacity-50"></i>
                                    <h6 class="mt-3 fw-bold">Belum Ada Kategori Buku</h6>
                                    <p class="small text-muted mb-3">
                                        @if(request('search'))
                                            Tidak ditemukan kategori yang sesuai kata kunci pencarian.
                                        @else
                                            Belum ada kategori yang didefinisikan dalam basis data.
                                        @endif
                                    </p>
                                    <a href="{{ route('kategori.create') }}" class="btn btn-sm btn-del">
                                        <i class="bi bi-plus-lg me-1"></i> Daftarkan Kategori Baru
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kategoris->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    Menampilkan <strong>{{ $kategoris->firstItem() }}</strong> - <strong>{{ $kategoris->lastItem() }}</strong> dari <strong>{{ $kategoris->total() }}</strong> kategori
                </div>
                <div>
                    {{ $kategoris->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>
