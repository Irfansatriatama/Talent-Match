<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPosition;
use Illuminate\Support\Facades\DB;

class JobPositionsSeeder extends Seeder
{
    /**
     * 
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('job_positions')->delete();

        $jobPositions = [
            [
                'name' => 'Software Developer',
                'description' => 'Membangun dan memelihara aplikasi perangkat lunak berkualitas tinggi.',
                'ideal_riasec_profile' => ['RIA', 'RIS', 'IRA', 'IRC'],
                'ideal_mbti_profile' => ['INTJ', 'INTP', 'ENTJ', 'ENTP', 'ISTJ', 'ISTP'],
            ],
            [
                'name' => 'Data Analyst',
                'description' => 'Menganalisis data untuk menemukan wawasan bisnis yang dapat ditindaklanjuti.',
                'ideal_riasec_profile' => ['IRA', 'IRC', 'CIR', 'ICR'],
                'ideal_mbti_profile' => ['ISTJ', 'INTJ', 'INTP', 'ESTJ'],
            ],
            [
                'name' => 'UI/UX Designer',
                'description' => 'Merancang pengalaman pengguna yang intuitif dan menarik secara visual.',
                'ideal_riasec_profile' => ['ARI', 'AIR', 'AIS', 'ASI'],
                'ideal_mbti_profile' => ['ENFP', 'INFP', 'ENFJ', 'INFJ', 'ISFP'],
            ],
            [
                'name' => 'Project Manager',
                'description' => 'Memimpin dan mengelola proyek teknologi dari awal hingga akhir.',
                'ideal_riasec_profile' => ['ESC', 'ECS', 'SEC', 'SCE'],
                'ideal_mbti_profile' => ['ENTJ', 'ENFJ', 'ESTJ', 'ESFJ'],
            ],
        ];

        foreach ($jobPositions as $positionData) {
            JobPosition::updateOrCreate(
                ['name' => $positionData['name']], 
                $positionData 
            );
        }
    }
}