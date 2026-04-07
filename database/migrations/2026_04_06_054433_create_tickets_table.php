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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('subject'); 
            $table->text('description'); 
            $table->string('location')->nullable(); 
            $table->enum('category', ['Hardware', 'Software', 'Network', 'Sistem RS'])->default('Hardware');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->enum('status', ['Open', 'On Progress', 'Pending', 'Closed'])->default('Open');
            $table->text('solution')->nullable();
            $table->timestamps();
            $table->timestamp('taken_at')->nullable();  
            $table->timestamp('closed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
