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
        Schema::create('peminjaman_inventaris', function (Blueprint $table) {
            $table->bigIncrements('id_peminjaman_inventaris');
            $table->unsignedBigInteger('id_peminjam');
            $table->enum('status_peminjaman', ['selesai', 'dipinjam', 'dikembalikan_sebagian'])->default('dipinjam');
            $table->string('no_telp');
            $table->string('nama_peminjam');
            $table->string('ni_peminjam');
            $table->enum('tipe_peminjam', ['dosen', 'mahasiswa']);
            $table->date('tanggal_pinjam');
            $table->date('tanggal_pengembalian')->nullable();
            $table->string('kegunaan');
            $table->string('referensi')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_inventaris');
    }
};
