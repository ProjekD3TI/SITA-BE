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
        Schema::create('proposal_ta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nim')->constrained('mahasiswa')->onDelete('cascade');
            $table->string('judul_proposal');
            $table->string('file_proposal');
            $table->smallInteger('status_proposal')->default(0);
            $table->timestamp('tanggal_unggah')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_ta');
    }
};
