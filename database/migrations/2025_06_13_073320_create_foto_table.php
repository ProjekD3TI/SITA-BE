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
        Schema::create('foto', function (Blueprint $table) {
            $table->bigIncrements('foto_id');
            $table->unsignedBigInteger('id_jenis_inventaris')->nullable()->index('id_jenis_inventaris');
            $table->unsignedBigInteger('id_ruang')->nullable()->index('id_ruang');
            $table->unsignedBigInteger('id_bhp')->nullable()->index('foto_ibfk_2');
            $table->string('foto_path');
            $table->date('created_at');
            $table->date('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foto');
    }
};
