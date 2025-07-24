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
        Schema::table('jenis_inventaris', function (Blueprint $table) {
            $table->foreign(['id_merk'], 'jenis_inventaris_ibfk_1')->references(['id_merk'])->on('merk')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_tipe'], 'jenis_inventaris_ibfk_2')->references(['id_tipe'])->on('tipe')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_inventaris', function (Blueprint $table) {
            $table->dropForeign('jenis_inventaris_ibfk_1');
            $table->dropForeign('jenis_inventaris_ibfk_2');
        });
    }
};
