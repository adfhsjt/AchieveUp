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
        Schema::create('prestasi_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prestasi_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->timestamps();

            $table->foreign('prestasi_id')->references('id')->on('prestasi')->onDelete('cascade');
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestasi_mahasiswa');
    }
};
