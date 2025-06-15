<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ujian_ta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_akhir_id')->constrained('tugas_akhir')->onDelete('restrict');
            $table->date('tanggal_ujian');
            $table->foreignId('ruang_id')->constrained('ruang')->onDelete('restrict');
            $table->foreignId('sesi_id')->constrained('sesi')->onDelete('restrict');
            $table->foreignId('semester_id')->constrained('semester')->onDelete('restrict');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->onDelete('restrict');
            $table->tinyInteger('status_kelulusan')->default(null)->nullable();//1: Lulus tanpa revisi, 2: Lulus revisi tanpa diuji lagi, 3: Belum lulus revisi & diuji lagi, 4: Tidak lulus ulang TA & diuji lagi
            $table->enum('status_ujian', ['non-aktif', 'aktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_ta');
    }
};
