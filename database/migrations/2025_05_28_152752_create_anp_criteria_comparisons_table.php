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
            
            // --- PERBAIKAN DI SINI ---
            // Kita secara manual memberikan nama indeks yang lebih pendek ('criteria_control_index')
            // sebagai argumen kedua pada method nullableMorphs().
            $table->nullableMorphs('control_criterionable', 'criteria_control_index');
            
            $table->string('compared_elements_type'); // Misal: App\Models\AnpElement atau App\Models\AnpCluster
            $table->json('comparison_data'); // Menyimpan matriks dan ID elemen/cluster yang dibandingkan
            $table->json('priority_vector')->nullable(); // Hasil perhitungan eigenvector
            $table->json('metadata')->nullable(); // Menambahkan dari saran Claude
            $table->timestamps();

            // Menambahkan index manual pada foreign key untuk performa
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
