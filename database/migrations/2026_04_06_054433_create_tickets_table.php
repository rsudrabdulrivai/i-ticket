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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Siapa yang lapor
            $table->string('subject'); // Judul kendala (misal: Printer Macet)
            $table->text('description'); // Detail masalah
            $table->string('location'); // Ruangan (IGD, ICU, Poli, dll)
            $table->enum('category', ['Hardware', 'Software', 'Network', 'Sistem RS'])->default('Hardware');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->enum('status', ['Open', 'On Progress', 'Pending', 'Closed'])->default('Open');
            $table->foreignId('technician_id')->nullable()->constrained('users'); // Teknisi yang menangani
            $table->text('solution')->nullable(); // Catatan perbaikan jika sudah selesai
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
