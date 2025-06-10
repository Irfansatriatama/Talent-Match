<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Test;

class TestsTableSeeder extends Seeder
{
    public function run()
    {
        Test::create([
            'test_id' => 1,
            'test_name' => 'Programming Skills Assessment',
            'test_type' => 'programming',
            'description' => 'Basic programming concepts including variables, control structures, loops, arrays, functions, and OOP.',
            'test_order' => 1,
            'time_limit_minutes' => 60
        ]);

        Test::create([
            'test_id' => 2,
            'test_name' => 'RIASEC Interest Inventory',
            'test_type' => 'riasec',
            'description' => 'Holland\'s RIASEC assessment to identify career interests across six dimensions.',
            'test_order' => 2,
            'time_limit_minutes' => 30
        ]);

        Test::create([
            'test_id' => 3,
            'test_name' => 'MBTI Personality Assessment',
            'test_type' => 'mbti',
            'description' => 'Myers-Briggs Type Indicator to assess personality preferences across four dichotomies.',
            'test_order' => 3,
            'time_limit_minutes' => 45
        ]);
    }
}