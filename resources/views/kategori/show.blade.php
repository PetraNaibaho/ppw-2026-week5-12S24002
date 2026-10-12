<x-layout>
    <x-slot:title>Kategori: {{ $kategori->nama_kategori }}</x-slot:title>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}" class="text-decoration-none text-muted">Kategori</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $kategori->nama_kategori }}</li>
        </ol>
    </nav>

    <!-- Category Header Card -->
    <div class="card card-custom mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-dark font-monospace px-2 py-1 fs-7">
                            Kode: {{ $kategori->kode_kategori }}
                        </span>
                        <span class="badge badge-del px-2 py-1 fs-7">
                            <i class="bi bi-collection me-1"></i> {{ $kategori->bukus->count() }} Koleksi Terdaftar
                        </span>
                    </div>
                    <h2 class="fw-bold text-dark mb-1">{{ $kategori->nama_kategori }}</h2>
                    <p class="text-muted small mb-0">
                        Kategori klasifikasi buku perpustakaan Institut Teknologi Del. Didaftarkan pada {{ $kategori->created_at->format('d F Y') }}.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                    <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-warning btn-sm text-dark fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i> Ubah Kategori
                    </a>
                    <a href="{{ route('buku.create') }}" class="btn btn-del btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Buku ke Kategori Ini
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Books in this Category -->
    <div class="card card-custom shadow-sm">
        <div class="card-header card-header-del py-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                <i class="bi bi-book-fill me-1"></i> Koleksi Buku dalam Kategori Ini
            </span>
            <span class="badge bg-secondary-subtle text-secondary small">
                {{ $kategori->bukus->count() }} Buku
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 140px;">ISBN</th>
                        <th>Judul Buku</th>
                        <th>Penulis & Penerbit</th>
                        <th class="text-center" style="width: 80px;">Tahun</th>
                        <th class="text-center" style="width: 90px;">Stok</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategori->bukus as $buku)
                        <tr>
                            <td class="text-center text-muted fw-medium small">{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace border small">
                                    {{ $buku->isbn }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('buku.show', $buku) }}" class="fw-semibold text-dark text-decoration-none hover-purple">
                                    {{ $buku->judul }}
                                </a>
                            </td>
                            <td class="small">
                                <div class="fw-medium text-dark">{{ $buku->penulis }}</div>
                                <div class="text-muted"><i class="bi bi-building me-1"></i>{{ $buku->penerbit }}</div>
                            </td>
                            <td class="text-center small text-secondary">
                                {{ $buku->tahun_terbit }}
                            </td>
                            <td class="text-center">
                                @if($buku->stok > 10)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        {{ $buku->stok }} eks
                                    </span>
                                @elseif($buku->stok > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        {{ $buku->stok }} eks
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Habis
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('buku.show', $buku) }}" class="btn btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-warning" title="Ubah Buku">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus buku \'{{ addslashes($buku->judul) }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Buku">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-folder-x fs-1 text-secondary opacity-50"></i>
                                    <h6 class="mt-3 fw-bold">Belum Ada Buku Dalam Kategori Ini</h6>
                                    <p class="small text-muted mb-3">
                                        Saat ini belum ada koleksi buku yang dialokasikan pada klasifikasi {{ $kategori->nama_kategori }}.
                                    </p>
                                    <a href="{{ route('buku.create') }}" class="btn btn-sm btn-del">
                                        <i class="bi bi-plus-lg me-1"></i> Tambahkan Buku Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
