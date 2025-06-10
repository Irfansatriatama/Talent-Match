<?php
// database/migrations/2024_XX_XX_create_user_test_progress_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_test_progress', function (Blueprint $table) {
            $table->id('user_test_progress_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->constrained('tests', 'test_id');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->decimal('score', 5, 2)->nullable();
            $table->string('result_summary')->nullable(); // RIASEC code or MBTI type
            $table->json('result_detail')->nullable(); // For additional data
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_seconds')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'test_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_test_progress');
    }
};