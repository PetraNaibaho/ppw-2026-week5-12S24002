<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BukuController extends Controller
{
    /**
     * Tampilkan katalog seluruh buku dengan Eager Loading (with('kategori'))
     * guna meniadakan masalah N+1 Query Problem secara mutlak.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $kategoriId = $request->query('kategori_id');

        // Menggunakan Eager Loading with('kategori') untuk optimasi performa kueri
        $query = Buku::with('kategori');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }

        $bukus = $query->latest()->paginate(10)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        // Statistik ringkas untuk dashboard antarmuka
        $totalBuku = Buku::count();
        $totalStok = Buku::sum('stok');
        $totalKategori = Kategori::count();

        return view('buku.index', compact('bukus', 'kategoris', 'search', 'kategoriId', 'totalBuku', 'totalStok', 'totalKategori'));
    }

    /**
     * Tampilkan formulir untuk menambahkan entri buku baru.
     */
    public function create(): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.create', compact('kategoris'));
    }

    /**
     * Simpan buku baru ke basis data dengan validasi ketat sisi server.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentYear = (int) date('Y');

        $validated = $request->validate([
            'isbn'         => ['required', 'string', 'size:13', 'regex:/^[0-9]{13}$/', 'unique:bukus,isbn'],
            'judul'        => ['required', 'string', 'min:5', 'max:255'],
            'penulis'      => ['required', 'string', 'min:3', 'max:150'],
            'penerbit'     => ['required', 'string', 'min:3', 'max:150'],
            'tahun_terbit' => ['required', 'integer', 'between:1900,' . ($currentYear + 1)],
            'kategori_id'  => ['required', 'exists:kategoris,id'],
            'stok'         => ['required', 'integer', 'min:0'],
            'sinopsis'     => ['nullable', 'string', 'max:1000'],
        ], [
            'isbn.required'         => 'Nomor ISBN wajib diisi.',
            'isbn.size'             => 'Nomor ISBN harus tepat berjumlah 13 digit.',
            'isbn.regex'            => 'Nomor ISBN hanya boleh memuat deretan angka numerik.',
            'isbn.unique'           => 'Nomor ISBN tersebut sudah terdaftar di basis data.',
            'judul.required'        => 'Judul buku wajib diisi.',
            'judul.min'             => 'Judul buku minimal harus memiliki 5 karakter.',
            'penulis.required'      => 'Nama penulis wajib diisi.',
            'penulis.min'           => 'Nama penulis minimal 3 karakter.',
            'penerbit.required'     => 'Nama penerbit wajib diisi.',
            'penerbit.min'          => 'Nama penerbit minimal 3 karakter.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.between'  => 'Tahun terbit harus antara tahun 1900 hingga ' . ($currentYear + 1) . '.',
            'kategori_id.required'  => 'Kategori buku wajib dipilih.',
            'kategori_id.exists'    => 'Kategori yang dipilih tidak valid atau belum terdaftar.',
            'stok.required'         => 'Jumlah stok buku wajib diisi.',
            'stok.min'              => 'Stok buku tidak boleh bernilai negatif (minimal 0).',
            'sinopsis.max'          => 'Sinopsis tidak boleh melebihi 1000 karakter.',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')
            ->with('success', "Buku '{$validated['judul']}' berhasil ditambahkan ke katalog!");
    }

    /**
     * Tampilkan detail spesifik buku beserta kategori terkait melalui Implicit Route Model Binding.
     */
    public function show(Buku $buku): View
    {
        $buku->load('kategori');

        return view('buku.show', compact('buku'));
    }

    /**
     * Tampilkan formulir edit buku dengan data eksisting melalui Implicit Route Model Binding.
     */
    public function edit(Buku $buku): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.edit', compact('buku', 'kategoris'));
    }

    /**
     * Perbarui data buku di basis data dengan validasi server-side toleran ID sendiri.
     */
    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $currentYear = (int) date('Y');

        $validated = $request->validate([
            'isbn'         => [
                'required',
                'string',
                'size:13',
                'regex:/^[0-9]{13}$/',
                Rule::unique('bukus', 'isbn')->ignore($buku->id),
            ],
            'judul'        => ['required', 'string', 'min:5', 'max:255'],
            'penulis'      => ['required', 'string', 'min:3', 'max:150'],
            'penerbit'     => ['required', 'string', 'min:3', 'max:150'],
            'tahun_terbit' => ['required', 'integer', 'between:1900,' . ($currentYear + 1)],
            'kategori_id'  => ['required', 'exists:kategoris,id'],
            'stok'         => ['required', 'integer', 'min:0'],
            'sinopsis'     => ['nullable', 'string', 'max:1000'],
        ], [
            'isbn.required'         => 'Nomor ISBN wajib diisi.',
            'isbn.size'             => 'Nomor ISBN harus tepat berjumlah 13 digit.',
            'isbn.regex'            => 'Nomor ISBN hanya boleh memuat deretan angka numerik.',
            'isbn.unique'           => 'Nomor ISBN tersebut sudah digunakan oleh buku lain.',
            'judul.required'        => 'Judul buku wajib diisi.',
            'judul.min'             => 'Judul buku minimal harus memiliki 5 karakter.',
            'penulis.required'      => 'Nama penulis wajib diisi.',
            'penulis.min'           => 'Nama penulis minimal 3 karakter.',
            'penerbit.required'     => 'Nama penerbit wajib diisi.',
            'penerbit.min'          => 'Nama penerbit minimal 3 karakter.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.between'  => 'Tahun terbit harus antara tahun 1900 hingga ' . ($currentYear + 1) . '.',
            'kategori_id.required'  => 'Kategori buku wajib dipilih.',
            'kategori_id.exists'    => 'Kategori yang dipilih tidak valid atau belum terdaftar.',
            'stok.required'         => 'Jumlah stok buku wajib diisi.',
            'stok.min'              => 'Stok buku tidak boleh bernilai negatif (minimal 0).',
            'sinopsis.max'          => 'Sinopsis tidak boleh melebihi 1000 karakter.',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')
            ->with('success', "Informasi buku '{$buku->judul}' berhasil diperbarui!");
    }

    /**
     * Hapus entri buku dari basis data.
     */
    public function destroy(Buku $buku): RedirectResponse
    {
        $judul = $buku->judul;
        $buku->delete();

        return redirect()->route('buku.index')
            ->with('success', "Buku '{$judul}' berhasil dihapus dari sistem perpustakaan!");
    }
}
