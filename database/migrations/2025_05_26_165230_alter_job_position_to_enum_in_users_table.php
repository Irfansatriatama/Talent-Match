<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Diperlukan untuk DB::statement jika menggunakan ENUM secara langsung

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Daftar posisi pekerjaan yang diizinkan
        $allowedPositions = ['Software Engineer', 'Data Analyst', 'Cybersecurity Specialist'];

        Schema::table('users', function (Blueprint $table) use ($allowedPositions) {
            if (Schema::hasColumn('users', 'job_position')) {
                // Mengubah kolom job_position menjadi ENUM.
                // PENTING: Jika ada data yang sudah ada di kolom job_position
                // yang tidak termasuk dalam $allowedPositions, migrasi ini bisa gagal
                // atau data tersebut akan diubah (misalnya menjadi string kosong atau nilai ENUM pertama)
                // tergantung pada driver database Anda (MySQL biasanya akan error atau mengambil nilai pertama).
                // Sebaiknya backup data atau bersihkan data yang tidak valid sebelum migrasi.
                
                // Cara Laravel dengan ->enum()
                $table->enum('job_position_new', $allowedPositions)->nullable()->after('phone');
            }
        });

        // Jika kolom lama ada, salin data jika memungkinkan (opsional, tergantung strategi migrasi data)
        // Untuk ENUM, konversi data lama mungkin perlu penanganan khusus jika tidak cocok.
        // Untuk kesederhanaan, kita asumsikan data lama bisa diabaikan atau akan diisi ulang.
        // Jika Anda perlu mempertahankan data lama yang valid:
        // DB::statement('UPDATE users SET job_position_new = job_position WHERE job_position IN ("'.implode('","', $allowedPositions).'")');
        
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
                 // Mengembalikan ke tipe string jika di-rollback
                 // Perlu membuat kolom baru, salin data, hapus enum, rename.
                $table->string('job_position_old')->nullable()->after('phone');
            }
        });

        // Salin data dari ENUM ke string (jika ada data)
        // DB::statement('UPDATE users SET job_position_old = CAST(job_position AS CHAR)');
        // Atau jika Anda yakin semua nilai ENUM aman untuk dikonversi:
        // DB::statement('UPDATE users SET job_position_old = job_position');


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