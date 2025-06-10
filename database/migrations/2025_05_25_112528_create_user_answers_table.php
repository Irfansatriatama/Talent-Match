<?php
// database/migrations/2024_XX_XX_create_user_answers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_answers', function (Blueprint $table) {
            $table->id('user_answer_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions', 'question_id');
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options', 'option_id');
            $table->tinyInteger('riasec_score_selected')->nullable(); // 1-5 for Likert scale
            $table->timestamp('answered_at');
            $table->timestamps();
            
            $table->unique(['user_id', 'question_id']);
            $table->index(['user_id', 'question_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_answers');
    }
};