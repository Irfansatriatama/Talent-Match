<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Membuat user admin default (opsional, bisa juga dari UserSeeder terpisah)
        User::factory()->create([
            'name' => 'Admin HR', // Ubah sesuai kebutuhan
            'email' => 'hr@talentmatch.app', // Ubah sesuai kebutuhan
            'password' => bcrypt('password'), // 'password' adalah contoh, ganti dengan password aman
            'role' => User::ROLE_HR, //
        ]);

        User::factory()->create([
            'name' => 'Candidate User', // Ubah sesuai kebutuhan
            'email' => 'candidate@talentmatch.app', // Ubah sesuai kebutuhan
            'password' => bcrypt('password'),
            'role' => User::ROLE_CANDIDATE, //
        ]);
        
        // Memanggil seeder lain yang sudah Anda buat
        $this->call([
            JobPositionsSeeder::class,
            AnpDefaultNetworkStructuresSeeder::class,
            TestsTableSeeder::class,
            ProgrammingQuestionsSeeder::class,
            MbtiQuestionsSeeder::class,
            RiasecQuestionsSeeder::class,
        ]);
    }
}