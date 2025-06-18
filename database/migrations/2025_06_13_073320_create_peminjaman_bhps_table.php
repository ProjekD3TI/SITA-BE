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
        Schema::create('peminjaman_bhps', function (Blueprint $table) {
            $table->bigIncrements('id_peminjaman_bhp');
            $table->unsignedBigInteger('id_bhp')->index('id_bhp');
            $table->unsignedBigInteger('id_peminjam');
            $table->enum('tipe_peminjam', ['mahasiswa', 'dosen']);
            $table->string('nama_peminjam');
            $table->string('ni_peminjam');
            $table->string('no_telp');
            $table->string('kegunaan');
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali')->nullable();
            $table->integer('jumlah_penggunaan');
            $table->enum('status_pengembalian', ['selesai', 'dipinjam', 'dikembalikan_sebagian'])->default('dipinjam');
            $table->integer('jumlah_kembali_bagus')->nullable()->default(0);
            $table->integer('jumlah_kembali_rusak')->default(0);
            $table->text('keterangan_pengembalian')->nullable();
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
        Schema::dropIfExists('peminjaman_bhps');
    }
};
