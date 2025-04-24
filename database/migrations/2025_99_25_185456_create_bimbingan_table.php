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
        Schema::create('bimbingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_akhir_id')->constrained('tugas_akhir')->onDelete('restrict');
            $table->text('catatan');
            $table->tinyInteger('bab')->default(1); // 1: Bab 1, 2: Bab 2, 3: Bab 3, 4: Bab 4, 5: Bab 5
            $table->tinyInteger('status_verifikasi')->default(0); // 0: Belum diverifikasi, 1: Diverifikasi, 2: Ditolak
            $table->date('tanggal_bimbingan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimbingan');
    }
};
