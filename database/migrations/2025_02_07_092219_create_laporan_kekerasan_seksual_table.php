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
        Schema::create('laporan_kekerasan_seksual', function (Blueprint $table) {
            $table->integer('laporan_kekerasan_id', true);
            $table->string('judul');
            $table->string('image_path');
            $table->integer('category_id')->index('category_id');
            $table->text('deskripsi');
            $table->dateTime('tanggal_kejadian');
            $table->string('nomor_telepon');
            $table->string('nomor_laporan_kekerasan');
            $table->string('nama_pelapor');
            $table->string('nim_pelapor');
            // Add new fields for umur_pelapor, profesi, and jenis_kelamin
            $table->enum('umur_pelapor', ['<20', '20-40', '40<'])->nullable();
            $table->string('profesi', 50)->nullable();
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable();
            // Add json fields for terlapor and bukti_pelanggaran
            $table->json('terlapor')->nullable();
            $table->json('bukti_pelanggaran')->nullable();
            // Existing saksi fields enhanced to JSON structure
            $table->json('saksi')->nullable();
            $table->string('lampiran_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kekerasan_seksual');
    }
};