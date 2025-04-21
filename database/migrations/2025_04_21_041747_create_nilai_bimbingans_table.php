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
            $table->integer('nilai_akhir_bimbingan');
            $table->integer('nilai_disiplin');
            $table->integer('nilai_komunikasi');
            $table->integer('nilai_skill_pengetahuan');
            $table->integer('nilai_tingkah_laku');
            $table->text('catatan_akhir_bimbingan')->nullable();
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
