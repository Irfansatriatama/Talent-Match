<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint; // Tetap di-import meskipun tidak langsung digunakan untuk ENUM
use Illuminate\Support\Facades\DB;      // Ditambahkan untuk DB::statement
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Mengubah tipe kolom 'role' menjadi ENUM dengan nilai 'candidate' atau 'hr'.
            // Memastikan tetap NOT NULL dan defaultnya 'candidate'.
            // PERHATIAN: Jika ada data di kolom 'role' yang sudah ada dan bukan 'candidate' atau 'hr',
            // migrasi ini bisa gagal atau data tersebut akan diubah paksa oleh MySQL.
            // Sebaiknya pastikan data yang ada sudah sesuai atau tangani kasusnya terlebih dahulu.
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('candidate', 'hr') NOT NULL DEFAULT 'candidate'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Mengembalikan tipe kolom 'role' ke VARCHAR jika migrasi di-rollback.
            // Panjang VARCHAR (255) dan properti NOT NULL DEFAULT 'candidate' disesuaikan
            // dengan asumsi keadaan kolom sebelum diubah menjadi ENUM.
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(255) NOT NULL DEFAULT 'candidate'");
        });
    }
};