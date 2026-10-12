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
        Schema::create('bukus', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 13)->unique();
            $table->string('judul', 255);
            $table->string('penulis', 150);
            $table->string('penerbit', 150);
            $table->integer('tahun_terbit');
            $table->foreignId('kategori_id')->constrained('kategoris')->cascadeOnDelete();
            $table->unsignedInteger('stok')->default(0);
            $table->text('sinopsis')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukus');
    }
};
