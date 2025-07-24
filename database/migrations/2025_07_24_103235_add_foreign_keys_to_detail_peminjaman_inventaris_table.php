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
        Schema::table('detail_peminjaman_inventaris', function (Blueprint $table) {
            $table->foreign(['id_peminjaman_inventaris'], 'detail_peminjaman_inventaris_ibfk_1')->references(['id_peminjaman_inventaris'])->on('peminjaman_inventaris')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_inventaris'], 'detail_peminjaman_inventaris_ibfk_2')->references(['id_inventaris'])->on('inventaris')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_peminjaman_inventaris', function (Blueprint $table) {
            $table->dropForeign('detail_peminjaman_inventaris_ibfk_1');
            $table->dropForeign('detail_peminjaman_inventaris_ibfk_2');
        });
    }
};
