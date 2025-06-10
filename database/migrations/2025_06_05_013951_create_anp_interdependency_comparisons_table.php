<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anp_interdependency_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anp_analysis_id')->constrained('anp_analyses')->onDelete('cascade');
            $table->foreignId('anp_dependency_id')->constrained('anp_dependencies')->onDelete('cascade');
            $table->json('comparison_data'); // Menyimpan matriks dan ID elemen sumber yang dibandingkan
            $table->json('priority_vector')->nullable(); // Hasil perhitungan eigenvector
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_interdependency_comparisons');
    }
};