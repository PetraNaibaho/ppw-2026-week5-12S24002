<x-layout>
    <x-slot:title>Tambah Buku Baru</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('buku.index') }}" class="text-decoration-none text-muted">Katalog Buku</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Buku Baru</li>
                </ol>
            </nav>

            <div class="card card-custom shadow-sm">
                <div class="card-header card-header-del py-3 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-file-earmark-plus-fill me-1"></i> Formulir Pendaftaran Buku Baru
                    </span>
                    <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <div class="fw-semibold mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                Terdapat kesalahan pada formulir yang Anda kirimkan:
                            </div>
                            <ul class="mb-0 small ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('buku.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- ISBN Field -->
                            <div class="col-md-6">
                                <label for="isbn" class="form-label fw-medium small text-dark">
                                    Nomor ISBN (13 Digit Angka) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text"
                                           name="isbn"
                                           id="isbn"
                                           class="form-control @error('isbn') is-invalid @enderror"
                                           placeholder="Contoh: 9786022987101"
                                           value="{{ old('isbn') }}"
                                           maxlength="13"
                                           required>
                                    @error('isbn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small">Wajib unik dan terdiri dari tepat 13 digit angka standar ISBN internasional.</div>
                            </div>

                            <!-- Kategori Dropdown -->
                            <div class="col-md-6">
                                <label for="kategori_id" class="form-label fw-medium small text-dark">
                                    Kategori Buku <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-bookmark-fill"></i></span>
                                    <select name="kategori_id" id="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kategori Buku --</option>
                                        @foreach($kategoris as $kategori)
                                            <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                                [{{ $kategori->kode_kategori }}] {{ $kategori->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kategori_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small">Relasi foreign key ke tabel <code>kategoris</code>.</div>
                            </div>

                            <!-- Judul Buku -->
                            <div class="col-12">
                                <label for="judul" class="form-label fw-medium small text-dark">
                                    Judul Lengkap Buku <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="judul"
                                       id="judul"
                                       class="form-control @error('judul') is-invalid @enderror"
                                       placeholder="Masukkan judul buku lengkap (minimal 5 karakter)..."
                                       value="{{ old('judul') }}"
                                       required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Penulis -->
                            <div class="col-md-6">
                                <label for="penulis" class="form-label fw-medium small text-dark">
                                    Nama Penulis / Pengarang <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-fill"></i></span>
                                    <input type="text"
                                           name="penulis"
                                           id="penulis"
                                           class="form-control @error('penulis') is-invalid @enderror"
                                           placeholder="Nama penulis atau pengarang..."
                                           value="{{ old('penulis') }}"
                                           required>
                                    @error('penulis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Penerbit -->
                            <div class="col-md-6">
                                <label for="penerbit" class="form-label fw-medium small text-dark">
                                    Penerbit <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                    <input type="text"
                                           name="penerbit"
                                           id="penerbit"
                                           class="form-control @error('penerbit') is-invalid @enderror"
                                           placeholder="Nama badan/perusahaan penerbit..."
                                           value="{{ old('penerbit') }}"
                                           required>
                                    @error('penerbit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tahun Terbit -->
                            <div class="col-md-6">
                                <label for="tahun_terbit" class="form-label fw-medium small text-dark">
                                    Tahun Terbit <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                    <input type="number"
                                           name="tahun_terbit"
                                           id="tahun_terbit"
                                           class="form-control @error('tahun_terbit') is-invalid @enderror"
                                           placeholder="Contoh: 2024"
                                           min="1900"
                                           max="{{ date('Y') + 1 }}"
                                           value="{{ old('tahun_terbit', date('Y')) }}"
                                           required>
                                    @error('tahun_terbit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Stok Buku -->
                            <div class="col-md-6">
                                <label for="stok" class="form-label fw-medium small text-dark">
                                    Jumlah Stok Eksemplar <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-boxes"></i></span>
                                    <input type="number"
                                           name="stok"
                                           id="stok"
                                           class="form-control @error('stok') is-invalid @enderror"
                                           placeholder="Minimal 0"
                                           min="0"
                                           value="{{ old('stok', 1) }}"
                                           required>
                                    @error('stok')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small">Stok minimal bernilai 0.</div>
                            </div>

                            <!-- Sinopsis / Ringkasan Buku -->
                            <div class="col-12">
                                <label for="sinopsis" class="form-label fw-medium small text-dark">
                                    Sinopsis / Deskripsi Ringkas Buku
                                </label>
                                <textarea name="sinopsis"
                                          id="sinopsis"
                                          rows="4"
                                          class="form-control @error('sinopsis') is-invalid @enderror"
                                          placeholder="Uraian singkat isi, ruang lingkup materi, atau ringkasan buku...">{{ old('sinopsis') }}</textarea>
                                @error('sinopsis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4 text-muted">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-del px-4 shadow-sm">
                                <i class="bi bi-save2 me-1"></i> Simpan Buku
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
