<?php
// database/migrations/2024_XX_XX_create_user_mbti_scores_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_mbti_scores', function (Blueprint $table) {
            $table->id('user_mbti_score_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('ei_score_e')->default(0);
            $table->integer('ei_score_i')->default(0);
            $table->integer('sn_score_s')->default(0);
            $table->integer('sn_score_n')->default(0);
            $table->integer('tf_score_t')->default(0);
            $table->integer('tf_score_f')->default(0);
            $table->integer('jp_score_j')->default(0);
            $table->integer('jp_score_p')->default(0);
            $table->decimal('ei_preference_strength', 5, 2);
            $table->decimal('sn_preference_strength', 5, 2);
            $table->decimal('tf_preference_strength', 5, 2);
            $table->decimal('jp_preference_strength', 5, 2);
            $table->char('mbti_type', 4);
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            $table->index(['user_id', 'calculated_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_mbti_scores');
    }
};