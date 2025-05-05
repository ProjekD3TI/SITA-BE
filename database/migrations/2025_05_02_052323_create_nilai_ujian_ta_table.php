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
            $table->integer('rerata_ujian_ta');
            $table->integer('kualitas_tata_laporan');
            $table->integer('kemampuan_teknis');
            $table->integer('kualitas_produk');
            $table->integer('presentasi');
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
