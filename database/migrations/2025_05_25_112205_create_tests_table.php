<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id('test_id');
            $table->string('test_name');
            $table->enum('test_type', ['programming', 'riasec', 'mbti']);
            $table->text('description')->nullable();
            $table->integer('test_order')->default(0);
            $table->integer('time_limit_minutes')->nullable();
            $table->timestamps();
            
            $table->unique('test_type');
            $table->index('test_order');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tests');
    }
};