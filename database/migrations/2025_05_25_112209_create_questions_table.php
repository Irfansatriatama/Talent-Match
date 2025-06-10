<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id('question_id');
            $table->foreignId('test_id')->constrained('tests', 'test_id')->onDelete('cascade');
            $table->text('question_text');
            $table->integer('question_order');
            $table->string('programming_category', 50)->nullable();
            $table->enum('riasec_dimension', ['R', 'I', 'A', 'S', 'E', 'C'])->nullable();
            $table->enum('mbti_dichotomy', ['EI', 'SN', 'TF', 'JP'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['test_id', 'question_order', 'is_active']);
            $table->index('programming_category');
            $table->index('riasec_dimension');
            $table->index('mbti_dichotomy');
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
};