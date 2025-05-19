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
        Schema::create('berkas', function (Blueprint $table) {
            $table->id();
            $table->string('berkasable_type'); // Tugas Akhir / Proposal
            $table->unsignedBigInteger('berkasable_id');
            $table->string('nama_berkas'); // Jenis file, misal: krs, file_proposal
            $table->string('file_path'); // Path file di storage
            $table->tinyInteger('status_valid')->default(0); // 0 = menunggu, 1 = valid, 2 = tidak valid
            $table->timestamps();

            $table->index(['berkasable_type', 'berkasable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
