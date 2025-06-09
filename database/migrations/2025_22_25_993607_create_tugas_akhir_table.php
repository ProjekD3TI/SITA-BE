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
        Schema::create('tugas_akhir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('restrict');
            $table->foreignId('dosen_pembimbing_id')->default(null)->nullable()->constrained('dosen')->onDelete('restrict');
            $table->foreignId('proposal_id')->default(null)->nullable()->constrained('proposal')->onDelete('restrict');
            $table->foreignId('semester_id')->default(null)->nullable()->constrained('semester')->onDelete('restrict');
            $table->foreignId('tahun_ajaran_id')->default(null)->nullable()->constrained('tahun_ajaran')->onDelete('restrict');
            $table->foreignId('nilai_bimbingan_id')->default(null)->nullable()->constrained('nilai_bimbingan')->onDelete('restrict');
            $table->foreignId('kategori_id')->default(null)->nullable()->constrained('kategori')->onDelete('restrict');
            $table->string('judul_tugas_akhir', 255)->default(null)->nullable();
            $table->double('nilai_proposal', 5, 2)->default(null)->nullable();
            $table->double('nilai_akhir', 5, 2)->default(null)->nullable();
            $table->text('catatan_proposal')->default(null)->nullable();
            $table->text('catatan_perbaikan')->default(null)->nullable();
            $table->tinyInteger('status_kelulusan')->default(null)->nullable();//1: Lulus tanpa revisi, 2: Lulus revisi tanpa diuji lagi, 3: Belum lulus revisi & diuji lagi, 4: Tidak lulus ulang TA & diuji lagi
            $table->tinyInteger('status_valid')->default(null)->nullable(); // null:data belum lengkap, 0:menunggu validasi, 1:valid, 2:tidak valid
            $table->tinyInteger('status_aktif')->default(1); // 0:non-aktif, 1:aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_akhir');
    }
};
