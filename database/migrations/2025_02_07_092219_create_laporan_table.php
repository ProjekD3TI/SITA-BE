<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->integer('laporan_id', true);
            $table->string('judul');
            $table->string('image_path');
            $table->integer('category_id')->index('category_id');
            $table->string('deskripsi');
            $table->string('nomor_telepon');
            $table->string('nomor_laporan');
            $table->string('nama_pelapor');
            $table->string('ni_pelapor');
            $table->string('profesi', 50)->nullable();
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable();
            $table->enum('umur_pelapor', ['<20', '20-40', '40<'])->nullable();
            // Add JSON columns for terlapor and saksi
            $table->json('terlapor')->nullable(); // Add this line
            $table->json('saksi')->nullable();    // Add this line
            $table->dateTime('tanggal_kejadian');
            $table->enum('status', ['unverified', 'verified', 'rejected', 'finished'])->default('unverified');
            $table->text('tanggapan')->nullable();
            $table->string('lampiran_link')->nullable();
            $table->bigInteger('user_id')->index('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};