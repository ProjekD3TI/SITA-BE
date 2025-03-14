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
        Schema::create('proposal', function (Blueprint $table) {
            $table->id();
            $table->string('judul_proposal', 255);
            $table->string('file_proposal');
            $table->string('file_transkrip');
            $table->tinyInteger('status_proposal')->default(0); // 0: Menunggu, 1: Diterima, 2: Ditolak
            $table->timestamp('tanggal_unggah')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal');
    }
};
