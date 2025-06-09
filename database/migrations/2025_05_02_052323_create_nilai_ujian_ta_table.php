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
        Schema::create('nilai_ujian_ta', function (Blueprint $table) {
            $table->id();
            $table->double('rerata_ujian_ta', 5, 2);
            $table->double('kualitas_tata_laporan', 5, 2);
            $table->double('kemampuan_teknis', 5, 2);
            $table->double('kualitas_produk', 5, 2);
            $table->double('presentasi', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_ujian_ta');
    }
};
