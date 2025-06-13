<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_riasec_scores', function (Blueprint $table) {
            $table->id('user_riasec_score_id');
            
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            $table->integer('r_score')->default(0)->comment('Realistic score');
            $table->integer('i_score')->default(0)->comment('Investigative score');
            $table->integer('a_score')->default(0)->comment('Artistic score');
            $table->integer('s_score')->default(0)->comment('Social score');
            $table->integer('e_score')->default(0)->comment('Enterprising score');
            $table->integer('c_score')->default(0)->comment('Conventional score');
        
            $table->string('riasec_code', 3)->nullable()->comment('3-letter RIASEC code');
            
            $table->timestamp('calculated_at')->nullable();
            
            $table->timestamps();
            
            $table->index('riasec_code');
            $table->index('calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_riasec_scores');
    }
};