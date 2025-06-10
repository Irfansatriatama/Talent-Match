<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anp_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anp_network_structure_id')->constrained('anp_network_structures')->onDelete('cascade');
            $table->foreignId('anp_cluster_id')->nullable()->constrained('anp_clusters')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_elements');
    }
};