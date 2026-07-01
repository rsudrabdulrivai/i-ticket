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
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Mengikat ke id di tabel users
            $table->string('title');
            $table->string('slug')->unique(); // Untuk URL ramah SEO (Clean URL)
            
            // Kolom Multi-Unit & Visibilitas
            $table->string('unit_owner'); // Menyimpan Unit pemilik (contoh: IT, SDM, Rekam Medis)
            $table->string('visibility')->default('Public'); // Public atau Internal
            
            $table->string('category'); // Informasi, Tutorial, Troubleshooting, SOP, dll.
            $table->longText('content'); // Isi materi / keilmuan format teks panjang / HTML
            $table->string('tags')->nullable(); // Tag opsional (bisa diisi string pisah koma)
            $table->enum('status', ['Draft', 'Published'])->default('Draft');
            $table->integer('views_count')->default(0); // Menghitung total pembaca
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};