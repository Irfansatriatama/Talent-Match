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
        Schema::create('anp_criteria_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anp_analysis_id')->constrained('anp_analyses')->onDelete('cascade');
            
            $table->nullableMorphs('control_criterionable', 'criteria_control_index');
            
            $table->string('compared_elements_type'); 
            $table->json('comparison_data'); 
            $table->json('priority_vector')->nullable(); 
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('anp_analysis_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anp_criteria_comparisons');
    }
};
