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
        Schema::create('jenis_inventaris', function (Blueprint $table) {
            $table->bigIncrements('id_jenis_inventaris');
            $table->string('nama_inventaris');
            $table->unsignedBigInteger('id_merk')->index('id_merk');
            $table->unsignedBigInteger('id_tipe')->index('id_tipe');
            $table->enum('peruntukan', ['alat_lab', 'inventaris_kantor']);
            $table->enum('status_import', ['produk_luar_negeri', 'produk_dalam_negeri']);
            $table->date('created_at');
            $table->date('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_inventaris');
    }
};
