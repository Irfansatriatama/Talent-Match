<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('job_position');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('job_position_id')->nullable()->after('phone')->constrained('job_positions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['job_position_id']);
            $table->dropColumn('job_position_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->enum('job_position', ['Software Engineer', 'Data Analyst', 'Cybersecurity Specialist'])->nullable()->after('phone');
        });
    }
};