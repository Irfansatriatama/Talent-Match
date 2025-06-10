<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anp_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anp_analysis_id')->constrained('anp_analyses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Candidate User ID
            $table->float('score', 8, 5); // Sesuaikan presisi jika perlu
            $table->integer('rank');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_results');
    }
};