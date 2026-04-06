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
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('tindak_lanjut')->nullable();
            $table->text('keterangan_it')->nullable();
            $table->string('kategori_perubahan')->nullable(); // Contoh: Perbaikan, Penggantian, Update
            $table->string('kategori_alat')->nullable();     // Contoh: Hardware, Software, Jaringan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
        });
    }
};
