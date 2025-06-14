<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anp_alternative_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anp_analysis_id')->constrained('anp_analyses')->onDelete('cascade');
            $table->foreignId('anp_element_id')->constrained('anp_elements')->onDelete('cascade');
            $table->json('comparison_data'); 
            $table->json('priority_vector')->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_alternative_comparisons');
    }
};