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
        Schema::create('peminjaman_ruangs', function (Blueprint $table) {
            $table->bigIncrements('id_peminjaman_ruang');
            $table->unsignedBigInteger('id_ruang')->index('id_ruang');
            $table->unsignedBigInteger('id_peminjam')->index('id_peminjam');
            $table->unsignedBigInteger('id_periode')->index('id_periode');
            $table->unsignedBigInteger('id_jenis_acara')->index('id_jenis_acara');
            $table->unsignedBigInteger('id_sesi_mulai')->index('fk_sesi_mulai');
            $table->unsignedBigInteger('id_sesi_selesai')->index('fk_sesi_selesai');
            $table->string('kepentingan');
            $table->string('surat')->nullable();
            $table->enum('status_peminjaman', ['MENUNGGU PERSETUJUAN', 'DISETUJUI', 'DITOLAK']);
            $table->string('no_telepon');
            $table->string('nama_peminjam');
            $table->string('nim_peminjam');
            $table->date('tanggal_peminjaman');
            $table->string('penanggung_jawab');
            $table->string('no_reservasi')->unique('no_reservasi');
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_ruangs');
    }
};
