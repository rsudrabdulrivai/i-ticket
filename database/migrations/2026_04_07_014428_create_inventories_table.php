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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique(); // Contoh: INV-IT-2024-001
            $table->string('name');
            $table->string('brand');
            $table->string('category'); // PC, Laptop, Printer, Router, dll
            $table->text('specification');
            $table->string('room'); // Lokasi: IGD, Poli Dalam, IT Room, dll
            $table->enum('status', ['ready', 'used', 'repair', 'broken'])->default('ready');
            $table->date('purchase_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
