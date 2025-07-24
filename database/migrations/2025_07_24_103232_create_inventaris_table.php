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
        Schema::create('inventaris', function (Blueprint $table) {
            $table->bigIncrements('id_inventaris');
            $table->unsignedBigInteger('id_jenis_inventaris')->index('id_jenis_inventaris');
            $table->string('no_inventaris')->unique('no_inventaris');
            $table->unsignedBigInteger('id_ruang')->index('id_ruang');
            $table->enum('kondisi', ['baik', 'rusak', 'hilang']);
            $table->boolean('status')->default(true);
            $table->integer('tahun_pengadaan');
            $table->string('qr_code_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};
