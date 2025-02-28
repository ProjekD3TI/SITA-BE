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
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->string('judul_ta');
            $table->string('krs');
            $table->string('persetujuan_pembimbing');
            $table->string('sertifikat_kompetensi');
            $table->string('toeic');
            $table->string('berita_acara_semhas');
            $table->string('naskah_ta');
            $table->string('masa_perpanjangan_studi')->default(null)->nullable();
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
