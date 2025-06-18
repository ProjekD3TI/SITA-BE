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
        Schema::table('inventaris', function (Blueprint $table) {
            $table->foreign(['id_jenis_inventaris'], 'inventaris_ibfk_1')->references(['id_jenis_inventaris'])->on('jenis_inventaris')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_ruang'], 'inventaris_ibfk_2')->references(['id_ruang'])->on('ruangs')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropForeign('inventaris_ibfk_1');
            $table->dropForeign('inventaris_ibfk_2');
        });
    }
};
