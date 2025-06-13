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
        User::factory()->create([
            'name' => 'Admin HR', 
            'email' => 'hr@talentmatch.app', 
            'password' => bcrypt('password'), 
            'role' => User::ROLE_HR, 
        ]);

        User::factory()->create([
            'name' => 'Candidate User',
            'email' => 'candidate@talentmatch.app', 
            'password' => bcrypt('password'),
            'role' => User::ROLE_CANDIDATE, 
        ]);
        
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