<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom ENUM yang lama.
            // Parameter kedua adalah daftar enum yang ada sekarang untuk memastikan MySQL bisa menemukannya.
            $table->dropColumn('job_position');
        });

        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom baru sebagai foreign key
            $table->foreignId('job_position_id')->nullable()->after('phone')->constrained('job_positions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Logika untuk rollback jika diperlukan
            $table->dropForeign(['job_position_id']);
            $table->dropColumn('job_position_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->enum('job_position', ['Software Engineer', 'Data Analyst', 'Cybersecurity Specialist'])->nullable()->after('phone');
        });
    }
};