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
        Schema::create('request_pembimbing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_akhir_id')->constrained('tugas_akhir')->onDelete('restrict');
            $table->foreignId('dosen_id')->constrained('dosen')->onDelete('restrict');
            $table->tinyInteger('status_request')->default(0); // 0: pending, 1: accepted, 2: rejected
            $table->timestamp('tanggal_request')->useCurrent();
            $table->timestamp('tanggal_respon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_pembimbing');
    }
};
