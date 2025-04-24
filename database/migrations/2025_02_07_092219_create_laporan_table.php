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
            $table->string('nim_pelapor');
            $table->string('profesi', 50)->after('ni_pelapor')->nullable();
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->after('profesi')->nullable();
            $table->enum('umur_pelapor', ['<20', '20-40', '40<'])->after('jenis_kelamin')->nullable();
            $table->dateTime('tanggal_kejadian');
            $table->enum('status', ['unverified', 'verified', 'rejected', 'finished'])->default('unverified');
            $table->text('tanggapan')->nullable();
            $table->string('lampiran_link')->nullable(); // Kolom baru yang ditambahkan
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