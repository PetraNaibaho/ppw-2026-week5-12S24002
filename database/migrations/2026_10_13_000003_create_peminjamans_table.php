<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buku_id')->constrained('bukus')->cascadeOnDelete();
            $table->string('nim_peminjam', 20)->index();
            $table->string('nama_peminjam', 150);
            $table->date('tanggal_pinjam');
            $table->unsignedSmallInteger('durasi_hari')->default(7);
            $table->date('tanggal_kembali')->nullable();
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
