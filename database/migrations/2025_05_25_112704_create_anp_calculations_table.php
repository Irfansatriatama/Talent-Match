<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('anp_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('calculation_date');
            $table->json('input_data');
            $table->json('anp_weights')->nullable();
            $table->json('final_rankings');
            $table->string('job_position')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['calculated_by', 'calculation_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('anp_calculations');
    }
};