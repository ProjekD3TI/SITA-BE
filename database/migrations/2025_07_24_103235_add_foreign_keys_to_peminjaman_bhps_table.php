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
        Schema::table('peminjaman_bhps', function (Blueprint $table) {
            $table->foreign(['id_bhp'], 'peminjaman_bhps_ibfk_1')->references(['id_bhp'])->on('bhps')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_bhps', function (Blueprint $table) {
            $table->dropForeign('peminjaman_bhps_ibfk_1');
        });
    }
};
