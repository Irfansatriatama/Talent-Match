<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ubah nama kolom 'location' menjadi 'job_position'
            // Pastikan doctrine/dbal terinstal: composer require doctrine/dbal
            if (Schema::hasColumn('users', 'location')) {
                $table->renameColumn('location', 'job_position');
            }
            // Ubah nama kolom 'about' menjadi 'profile_summary'
            if (Schema::hasColumn('users', 'about')) {
                $table->renameColumn('about', 'profile_summary');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan nama kolom 'job_position' menjadi 'location'
            if (Schema::hasColumn('users', 'job_position')) {
                $table->renameColumn('job_position', 'location');
            }
            // Kembalikan nama kolom 'profile_summary' menjadi 'about'
            if (Schema::hasColumn('users', 'profile_summary')) {
                $table->renameColumn('profile_summary', 'about');
            }
        });
    }
};