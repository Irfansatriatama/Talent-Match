<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anp_analysis_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anp_analysis_id')->constrained('anp_analyses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            $table->timestamps();

            $table->unique(['anp_analysis_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_analysis_candidates');
    }
};