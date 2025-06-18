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
        Schema::create('bhps', function (Blueprint $table) {
            $table->bigIncrements('id_bhp');
            $table->string('nama_bhp');
            $table->integer('kondisi_bagus');
            $table->integer('kondisi_rusak');
            $table->string('qr_code_path')->nullable();
            $table->string('peruntukan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhps');
    }
};
