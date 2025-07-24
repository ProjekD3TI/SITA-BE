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
        Schema::table('foto', function (Blueprint $table) {
            $table->foreign(['id_bhp'], 'foto_ibfk_1')->references(['id_bhp'])->on('bhps')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_jenis_inventaris'], 'foto_ibfk_2')->references(['id_jenis_inventaris'])->on('jenis_inventaris')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_ruang'], 'foto_ibfk_3')->references(['id_ruang'])->on('ruangs')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foto', function (Blueprint $table) {
            $table->dropForeign('foto_ibfk_1');
            $table->dropForeign('foto_ibfk_2');
            $table->dropForeign('foto_ibfk_3');
        });
    }
};
