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
        Schema::create('dosen_penguji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('dosen')->onDelete('restrict');
            $table->foreignId('tugas_akhir_id')->constrained('tugas_akhir')->onDelete('restrict');
            $table->foreignId('nilai_ujian_ta_id')->nullable()->constrained('nilai_ujian_ta')->onDelete('restrict');
            $table->enum('jabatan_penguji', ['ketua', 'sekretaris', 'penguji'])->default('penguji'); // jabatan penguji: ketua, sekretaris, penguji
            $table->text('revisi')->nullable();
            $table->text('catatan_semhas')->nullable();
            $table->timestamps();

            // untuk menghindari duplikasi
            $table->unique(['dosen_id', 'tugas_akhir_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_penguji');
    }
};
