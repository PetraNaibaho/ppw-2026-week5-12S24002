<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del)
|--------------------------------------------------------------------------
|
| Routing RESTful terstruktur menggunakan Route::resource() yang mendaftarkan
| 7 aksi baku CRUD untuk entitas Buku dan Kategori, dilengkapi Implicit
| Route Model Binding.
|
*/

// Redirect halaman utama ke daftar katalog buku
Route::get('/', function () {
    return redirect()->route('buku.index');
});

// Resource Controller untuk Entitas Kategori Buku (7 method CRUD)
Route::resource('kategori', KategoriController::class);

// Resource Controller untuk Entitas Buku (7 method CRUD)
Route::resource('buku', BukuController::class);
