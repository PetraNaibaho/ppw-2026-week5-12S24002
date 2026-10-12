<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Tampilkan daftar seluruh kategori beserta jumlah buku yang berelasi (withCount).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Kategori::withCount('bukus');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_kategori', 'like', "%{$search}%")
                  ->orWhere('nama_kategori', 'like', "%{$search}%");
            });
        }

        $kategoris = $query->latest()->paginate(10)->withQueryString();

        return view('kategori.index', compact('kategoris', 'search'));
    }

    /**
     * Tampilkan formulir tambah kategori baru.
     */
    public function create(): View
    {
        return view('kategori.create');
    }

    /**
     * Simpan data kategori baru ke basis data dengan validasi server-side ketat.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:kategoris,kode_kategori'],
            'nama_kategori' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'kode_kategori.required'   => 'Kode kategori wajib diisi.',
            'kode_kategori.max'        => 'Kode kategori maksimal terdiri dari 10 karakter.',
            'kode_kategori.alpha_dash' => 'Kode kategori hanya boleh memuat huruf, angka, strip, atau garis bawah.',
            'kode_kategori.unique'     => 'Kode kategori tersebut telah terdaftar.',
            'nama_kategori.required'   => 'Nama kategori wajib diisi.',
            'nama_kategori.min'        => 'Nama kategori minimal harus memiliki 3 karakter.',
            'nama_kategori.max'        => 'Nama kategori maksimal terdiri dari 100 karakter.',
        ]);

        Kategori::create($validated);

        return redirect()->route('kategori.index')
            ->with('success', "Kategori '{$validated['nama_kategori']}' berhasil ditambahkan!");
    }

    /**
     * Tampilkan detail kategori beserta daftar koleksi buku di dalamnya.
     */
    public function show(Kategori $kategori): View
    {
        $kategori->load(['bukus' => function ($query) {
            $query->latest();
        }]);

        return view('kategori.show', compact('kategori'));
    }

    /**
     * Tampilkan formulir edit kategori dengan Implicit Route Model Binding.
     */
    public function edit(Kategori $kategori): View
    {
        return view('kategori.edit', compact('kategori'));
    }

    /**
     * Perbarui data kategori di basis data dengan validasi server-side unik bertoleransi.
     */
    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => [
                'required',
                'string',
                'max:10',
                'alpha_dash',
                Rule::unique('kategoris', 'kode_kategori')->ignore($kategori->id),
            ],
            'nama_kategori' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'kode_kategori.required'   => 'Kode kategori wajib diisi.',
            'kode_kategori.max'        => 'Kode kategori maksimal terdiri dari 10 karakter.',
            'kode_kategori.alpha_dash' => 'Kode kategori hanya boleh memuat huruf, angka, strip, atau garis bawah.',
            'kode_kategori.unique'     => 'Kode kategori tersebut telah terdaftar.',
            'nama_kategori.required'   => 'Nama kategori wajib diisi.',
            'nama_kategori.min'        => 'Nama kategori minimal harus memiliki 3 karakter.',
            'nama_kategori.max'        => 'Nama kategori maksimal terdiri dari 100 karakter.',
        ]);

        $kategori->update($validated);

        return redirect()->route('kategori.index')
            ->with('success', "Kategori '{$kategori->nama_kategori}' berhasil diperbarui!");
    }

    /**
     * Hapus data kategori dari basis data.
     */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        $nama = $kategori->nama_kategori;
        $kategori->delete();

        return redirect()->route('kategori.index')
            ->with('success', "Kategori '{$nama}' beserta relasi buku terkait berhasil dihapus!");
    }
}
