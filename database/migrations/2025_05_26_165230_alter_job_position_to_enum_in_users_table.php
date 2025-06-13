<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $allowedPositions = ['Software Engineer', 'Data Analyst', 'Cybersecurity Specialist'];

        Schema::table('users', function (Blueprint $table) use ($allowedPositions) {
            if (Schema::hasColumn('users', 'job_position')) {
                $table->enum('job_position_new', $allowedPositions)->nullable()->after('phone');
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'job_position')) {
                $table->dropColumn('job_position');
            }
            if (Schema::hasColumn('users', 'job_position_new')) {
                $table->renameColumn('job_position_new', 'job_position');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'job_position')) {
                $table->string('job_position_old')->nullable()->after('phone');
            }
        });



        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'job_position')) {
                $table->dropColumn('job_position');
            }
            if (Schema::hasColumn('users', 'job_position_old')) {
                $table->renameColumn('job_position_old', 'job_position');
            }
        });
    }
};