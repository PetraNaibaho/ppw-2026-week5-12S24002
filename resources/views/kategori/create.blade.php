<x-layout>
    <x-slot:title>Tambah Kategori Buku Baru</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}" class="text-decoration-none text-muted">Kategori</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Kategori Baru</li>
                </ol>
            </nav>

            <div class="card card-custom shadow-sm">
                <div class="card-header card-header-del py-3 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-folder-plus me-1"></i> Formulir Tambah Kategori
                    </span>
                    <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <div class="fw-semibold mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                Periksa input data kategori berikut:
                            </div>
                            <ul class="mb-0 small ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('kategori.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="kode_kategori" class="form-label fw-medium small text-dark">
                                Kode Singkat Kategori <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                                <input type="text"
                                       name="kode_kategori"
                                       id="kode_kategori"
                                       class="form-control text-uppercase @error('kode_kategori') is-invalid @enderror"
                                       placeholder="Contoh: RPL, TIK, DSN"
                                       maxlength="10"
                                       value="{{ old('kode_kategori') }}"
                                       required>
                                @error('kode_kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Maksimal 10 karakter alfanumerik (wajib unik di sistem).</div>
                        </div>

                        <div class="mb-4">
                            <label for="nama_kategori" class="form-label fw-medium small text-dark">
                                Nama Lengkap Klasifikasi Kategori <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-tag-fill"></i></span>
                                <input type="text"
                                       name="nama_kategori"
                                       id="nama_kategori"
                                       class="form-control @error('nama_kategori') is-invalid @enderror"
                                       placeholder="Contoh: Rekayasa Perangkat Lunak"
                                       maxlength="100"
                                       value="{{ old('nama_kategori') }}"
                                       required>
                                @error('nama_kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Nama bidang keilmuan atau topik klasifikasi buku (minimal 3 karakter).</div>
                        </div>

                        <hr class="my-4 text-muted">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-del px-4 shadow-sm">
                                <i class="bi bi-save2 me-1"></i> Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
