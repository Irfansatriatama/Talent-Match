<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anp_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('job_position_id')->constrained('job_positions')->onDelete('restrict');
            $table->foreignId('anp_network_structure_id')->constrained('anp_network_structures')->onDelete('restrict');
            $table->foreignId('hr_user_id')->constrained('users')->onDelete('cascade'); // Asumsi tabel users sudah ada
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_analyses');
    }
};