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
        Schema::create('penilaian_proposal_ta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_proposal_ta')->constrained('proposal_ta')->onDelete('cascade');
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
        Schema::dropIfExists('penilaian_proposal_ta');
    }
};
