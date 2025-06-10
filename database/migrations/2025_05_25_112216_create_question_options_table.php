<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id('option_id');
            $table->foreignId('question_id')->constrained('questions', 'question_id')->onDelete('cascade');
            $table->text('option_text');
            $table->boolean('is_correct_programming')->nullable();
            $table->char('mbti_pole_represented', 1)->nullable();
            $table->integer('display_order');
            $table->timestamps();
            
            $table->index(['question_id', 'display_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_options');
    }
};