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
            $table->foreignId('id_ujian_ta')->constrained('ujian_ta')->onDelete('cascade');
            $table->foreignId('nip')->constrained('dosen')->onDelete('cascade');
            $table->unsignedTinyInteger('nilai')->default(0);
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
