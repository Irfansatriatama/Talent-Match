<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anp_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anp_network_structure_id')->constrained('anp_network_structures')->onDelete('cascade');
            $table->morphs('sourceable'); 
            $table->morphs('targetable'); 
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_dependencies');
    }
};