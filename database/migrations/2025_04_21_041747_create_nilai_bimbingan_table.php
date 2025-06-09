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
        Schema::create('nilai_bimbingan', function (Blueprint $table) {
            $table->id();
            $table->integer('rerata_bimbingan');
            $table->integer('disiplin');
            $table->integer('komunikasi');
            $table->integer('skill_pengetahuan');
            $table->integer('tingkah_laku');
            $table->text('catatan_akhir_bimbingan')->default(null)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_bimbingan');
    }
};
