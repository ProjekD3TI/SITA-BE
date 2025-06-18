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
        Schema::create('detail_peminjaman_inventaris', function (Blueprint $table) {
            $table->bigIncrements('id_detail_peminjaman');
            $table->unsignedBigInteger('id_peminjaman_inventaris')->index('id_peminjaman_inventaris');
            $table->unsignedBigInteger('id_inventaris')->index('id_inventaris');
            $table->text('catatan_pengembalian')->nullable();
            $table->enum('kondisi_akhir_pengembalian', ['baik', 'rusak', 'hilang'])->nullable();
            $table->boolean('status')->default(false);
            $table->date('tanggal_kembali')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_peminjaman_inventaris');
    }
};
