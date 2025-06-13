<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anp_matrix_consistencies', function (Blueprint $table) {
            $table->id();
            $table->morphs('matrixable'); 
            $table->float('consistency_ratio');
            $table->boolean('is_consistent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_matrix_consistencies');
    }
};