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
        Schema::table('jabatan_dosen', function (Blueprint $table) {
            $table->foreign(['dosen_id'])->references(['id'])->on('dosen')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['jabatan_id'])->references(['id'])->on('jabatan')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatan_dosen', function (Blueprint $table) {
            $table->dropForeign('jabatan_dosen_dosen_id_foreign');
            $table->dropForeign('jabatan_dosen_jabatan_id_foreign');
        });
    }
};
