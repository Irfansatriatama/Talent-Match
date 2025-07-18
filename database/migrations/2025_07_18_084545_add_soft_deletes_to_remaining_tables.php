<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToRemainingTables extends Migration
{
    public function up()
    {
        // Tambahkan soft deletes ke anp_analyses
        Schema::table('anp_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('anp_analyses', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Tambahkan soft deletes ke job_positions
        Schema::table('job_positions', function (Blueprint $table) {
            if (!Schema::hasColumn('job_positions', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Tambahkan kolom untuk menyimpan data kalkulasi jika belum ada
        Schema::table('anp_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('anp_analyses', 'calculation_data')) {
                $table->json('calculation_data')->nullable();
            }
            if (!Schema::hasColumn('anp_analyses', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('anp_analyses', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['calculation_data', 'completed_at']);
        });

        Schema::table('job_positions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}