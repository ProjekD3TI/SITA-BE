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
            $table->string('judul_tugas_akhir', 255)->default(null)->nullable();
            $table->string('krs', 150)->default(null)->nullable();
            $table->string('persetujuan_pembimbing', 150)->default(null)->nullable();
            $table->string('sertifikat_kompetensi', 150)->default(null)->nullable();
            $table->string('toeic', 150)->default(null)->nullable();
            $table->string('berita_acara_semhas', 150)->default(null)->nullable();
            $table->string('naskah_ta', 150)->default(null)->nullable();
            $table->string('masa_perpanjangan_studi', 150)->default(null)->nullable();
            $table->integer('nilai_proposal')->default(null)->nullable();
            $table->integer('nilai_bimbingan')->default(null)->nullable();
            $table->integer('nilai_akhir')->default(null)->nullable();
            $table->text('catatan_proposal')->default(null)->nullable();
            $table->string('file_transkrip', 255)->default(null)->nullable();
            $table->tinyInteger('status_valid')->default(null)->nullable(); // null:data belum lengkap, 0:menunggu validasi, 1:valid, 2:tidak valid
            $table->tinyInteger('status_aktif')->default(1); // 0:non-aktif, 1:aktif, 2:selesai
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
