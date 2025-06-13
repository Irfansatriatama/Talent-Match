<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class ANPService
{
    private $jobProfiles = [
        'software_developer' => [
            'riasec' => ['RIA', 'RIS', 'IRA', 'IRC'],
            'mbti' => ['INTJ', 'INTP', 'ENTJ', 'ENTP', 'ISTJ', 'ISTP']
        ],
        'data_analyst' => [
            'riasec' => ['IRA', 'IRC', 'CIR', 'ICR'],
            'mbti' => ['ISTJ', 'INTJ', 'INTP', 'ESTJ']
        ],
        'ui_ux_designer' => [
            'riasec' => ['ARI', 'AIR', 'AIS', 'ASI'],
            'mbti' => ['ENFP', 'INFP', 'ENFJ', 'INFJ', 'ISFP']
        ],
        'project_manager' => [
            'riasec' => ['ESC', 'ECS', 'SEC', 'SCE'],
            'mbti' => ['ENTJ', 'ENFJ', 'ESTJ', 'ESFJ']
        ],
        'system_analyst' => [
            'riasec' => ['ICR', 'IRC', 'CIR', 'RIC'],
            'mbti' => ['INTJ', 'ISTJ', 'INTP', 'ENTP']
        ],
        'quality_assurance' => [
            'riasec' => ['CIR', 'CRI', 'ICR', 'RCI'],
            'mbti' => ['ISTJ', 'ISFJ', 'ESTJ', 'INTJ']
        ],
        'business_analyst' => [
            'riasec' => ['CES', 'CSE', 'ECS', 'ICS'],
            'mbti' => ['ENTJ', 'ESTJ', 'INTJ', 'ISTJ']
        ],
        'network_engineer' => [
            'riasec' => ['RIC', 'RCI', 'IRC', 'CRI'],
            'mbti' => ['ISTJ', 'ISTP', 'INTJ', 'INTP']
        ],
        'default' => [
            'riasec' => ['RIA', 'IRA', 'IRC'],
            'mbti' => ['INTJ', 'INTP', 'ENTJ', 'ISTJ']
        ]
    ];

    public function calculate(Collection $candidates, array $weights, string $jobPosition = 'default')
    {
        $matrix = [];
        $normalizedJobPosition = $this->normalizeJobPosition($jobPosition);
        
        foreach ($candidates as $candidate) {
            $programmingScore = $candidate->testProgress->where('test_id', 1)->first()->score ?? 0;
            $riasecCode = $candidate->testProgress->where('test_id', 2)->first()->result_summary ?? '';
            $mbtiType = $candidate->latestMbtiScore->mbti_type ?? '';
            
            $criteria = [
                'programming' => $programmingScore / 100,
                'riasec' => $this->calculateRiasecMatch($riasecCode, $normalizedJobPosition),
                'mbti' => $this->calculateMbtiFit($mbtiType, $normalizedJobPosition),
            ];
            
            $weightedScore = 0;
            foreach ($criteria as $key => $value) {
                $criteriaKey = str_replace('_match', '', str_replace('_fit', '', $key));
                $weightedScore += $value * ($weights[$criteriaKey] ?? 0.33);
            }
            
            $matrix[] = [
                'user_id' => $candidate->id,
                'name' => $candidate->name,
                'email' => $candidate->email,
                'criteria_scores' => $criteria,
                'weighted_score' => round($weightedScore, 4),
                'rank' => 0,
                'details' => [
                    'programming_score' => $programmingScore,
                    'riasec_code' => $riasecCode,
                    'mbti_type' => $mbtiType
                ]
            ];
        }
        
        usort($matrix, function($a, $b) {
            return $b['weighted_score'] <=> $a['weighted_score'];
        });
        
        foreach ($matrix as $index => &$item) {
            $item['rank'] = $index + 1;
        }
        
        return $matrix;
    }
    
    private function normalizeJobPosition(string $jobPosition): string
    {
        $normalized = strtolower(str_replace(' ', '_', trim($jobPosition)));
        
        return array_key_exists($normalized, $this->jobProfiles) ? $normalized : 'default';
    }
    
    private function calculateRiasecMatch(string $code, string $jobPosition): float
    {
        if (empty($code)) return 0;
        
        $idealCodes = $this->jobProfiles[$jobPosition]['riasec'] ?? $this->jobProfiles['default']['riasec'];
        
        if (in_array($code, $idealCodes)) {
            return 1.0;
        }
        
        $score = 0;
        $codeChars = str_split($code);
        
        foreach ($idealCodes as $idealCode) {
            $idealChars = str_split($idealCode);
            $matchCount = 0;
            
            for ($i = 0; $i < min(3, strlen($code), strlen($idealCode)); $i++) {
                if (isset($codeChars[$i]) && isset($idealChars[$i]) && $codeChars[$i] === $idealChars[$i]) {
                    $matchCount += (3 - $i) * 0.15; 
                }
            }
            
            foreach ($codeChars as $char) {
                if (in_array($char, $idealChars)) {
                    $matchCount += 0.1;
                }
            }
            
            $score = max($score, min($matchCount, 1.0));
        }
        
        return round($score, 2);
    }
    
    private function calculateMbtiFit(string $type, string $jobPosition): float
    {
        if (empty($type) || strlen($type) !== 4) return 0;
        
        $idealTypes = $this->jobProfiles[$jobPosition]['mbti'] ?? $this->jobProfiles['default']['mbti'];
        
        if (in_array($type, $idealTypes)) {
            return 1.0;
        }
        
        $score = 0;
        $typeChars = str_split($type);
        
        foreach ($idealTypes as $idealType) {
            $idealChars = str_split($idealType);
            $matchScore = 0;
            
            for ($i = 0; $i < 4; $i++) {
                if ($typeChars[$i] === $idealChars[$i]) {
                    switch ($i) {
                        case 0: // E/I
                            $matchScore += 0.2;
                            break;
                        case 1: // S/N
                            $matchScore += 0.3;
                            break;
                        case 2: // T/F
                            $matchScore += 0.3;
                            break;
                        case 3: // J/P
                            $matchScore += 0.2;
                            break;
                    }
                }
            }
            
            $score = max($score, $matchScore);
        }
        
        return round($score, 2);
    }
    
    public function getJobPositions(): array
    {
        $positions = [];
        foreach (array_keys($this->jobProfiles) as $key) {
            if ($key !== 'default') {
                $positions[$key] = ucwords(str_replace('_', ' ', $key));
            }
        }
        return $positions;
    }
}