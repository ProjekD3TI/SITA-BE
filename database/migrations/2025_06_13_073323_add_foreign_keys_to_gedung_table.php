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
        Schema::table('gedung', function (Blueprint $table) {
            $table->foreign(['id_lantai'], 'gedung_ibfk_1')->references(['id_lantai'])->on('lantai_gedung')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gedung', function (Blueprint $table) {
            $table->dropForeign('gedung_ibfk_1');
        });
    }
};
