<?php

namespace App\Services;

use App\Models\Test;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserMbtiScore;

class TestScoringService
{
    public function calculateScore(Test $test, User $user)
    {
        $userAnswers = UserAnswer::where('user_id', $user->id)
                                ->whereHas('question', function($q) use ($test) {
                                    $q->where('test_id', $test->test_id);
                                })
                                ->with(['question', 'selectedOption'])
                                ->get();
        
        switch ($test->test_type) {
            case 'programming':
                return $this->scoreProgramming($userAnswers);
            case 'riasec':
                return $this->scoreRiasec($userAnswers);
            case 'mbti':
                return $this->scoreMbti($userAnswers);
        }
    }
    
    private function scoreProgramming($userAnswers)
    {
        $correct = 0;
        $total = 20;
        
        foreach ($userAnswers as $answer) {
            if ($answer->selectedOption && $answer->selectedOption->is_correct_programming) {
                $correct++;
            }
        }
        
        $score = ($correct / $total) * 100;
        
        return [
            'score' => $score,
            'summary' => "{$correct}/{$total} correct",
            'correct_count' => $correct,
            'total_count' => $total
        ];
    }
    
    private function scoreRiasec($userAnswers)
    {
        $dimensions = [
            'R' => 0, 'I' => 0, 'A' => 0, 
            'S' => 0, 'E' => 0, 'C' => 0
        ];
        
        foreach ($userAnswers as $answer) {
            $dimension = $answer->question->riasec_dimension;
            $dimensions[$dimension] += $answer->riasec_score_selected;
        }
        
        arsort($dimensions);
        $top3 = array_slice(array_keys($dimensions), 0, 3);
        $code = implode('', $top3);
        
        return [
            'summary' => $code,
            'dimensions' => $dimensions,
            'top_3' => $top3
        ];
    }
    
    private function scoreMbti($userAnswers)
    {
        $scores = [
            'EI' => ['E' => 0, 'I' => 0],
            'SN' => ['S' => 0, 'N' => 0],
            'TF' => ['T' => 0, 'F' => 0],
            'JP' => ['J' => 0, 'P' => 0]
        ];
        
        foreach ($userAnswers as $answer) {
            if ($answer->selectedOption) {
                $dichotomy = $answer->question->mbti_dichotomy;
                $pole = $answer->selectedOption->mbti_pole_represented;
                $scores[$dichotomy][$pole]++;
            }
        }
        
        $mbtiType = '';
        $strengths = [];
        $rawScores = [];
        
        foreach ($scores as $dichotomy => $poles) {
            $total = array_sum($poles);
            $dominant = array_keys($poles, max($poles))[0];
            $mbtiType .= $dominant;
            
            if ($total > 0) {
                $strengths[$dichotomy] = (max($poles) / $total) * 100;
            }
            
            $rawScores[strtolower($dichotomy) . '_raw_' . strtolower(array_keys($poles)[0])] = $poles[array_keys($poles)[0]];
            $rawScores[strtolower($dichotomy) . '_raw_' . strtolower(array_keys($poles)[1])] = $poles[array_keys($poles)[1]];
        }
        
        return [
            'summary' => $mbtiType,
            'type' => $mbtiType,
            'strengths' => $strengths,
            'raw_scores' => $rawScores,
            'detailed_scores' => $scores
        ];
    }
    
    public function saveMbtiDetailedScores(User $user, array $scoreData)
    {
        $data = [
            'user_id' => $user->id,
            'mbti_type' => $scoreData['type'],
            'ei_preference_strength' => $scoreData['strengths']['EI'] ?? 50,
            'sn_preference_strength' => $scoreData['strengths']['SN'] ?? 50,
            'tf_preference_strength' => $scoreData['strengths']['TF'] ?? 50,
            'jp_preference_strength' => $scoreData['strengths']['JP'] ?? 50,
            'calculated_at' => now()
        ];

        if (isset($scoreData['detailed_scores'])) {
        $detailedScores = $scoreData['detailed_scores'];
        $data['ei_score_e'] = $detailedScores['EI']['E'] ?? 0;
        $data['ei_score_i'] = $detailedScores['EI']['I'] ?? 0;
        $data['sn_score_s'] = $detailedScores['SN']['S'] ?? 0;
        $data['sn_score_n'] = $detailedScores['SN']['N'] ?? 0;
        $data['tf_score_t'] = $detailedScores['TF']['T'] ?? 0;
        $data['tf_score_f'] = $detailedScores['TF']['F'] ?? 0;
        $data['jp_score_j'] = $detailedScores['JP']['J'] ?? 0;
        $data['jp_score_p'] = $detailedScores['JP']['P'] ?? 0;
    } else {
        // Fallback jika struktur 'detailed_scores' tidak ada, meskipun seharusnya ada dari scoreMbti()
        // Atau jika Anda memang ingin menggunakan 'raw_scores' yang memiliki format 'ei_raw_e',
        // maka mapping manual diperlukan:
        // $mapping = [
        //     'ei_raw_e' => 'ei_score_e', 'ei_raw_i' => 'ei_score_i',
        //     'sn_raw_s' => 'sn_score_s', 'sn_raw_n' => 'sn_score_n',
        //     'tf_raw_t' => 'tf_score_t', 'tf_raw_f' => 'tf_score_f',
        //     'jp_raw_j' => 'jp_score_j', 'jp_raw_p' => 'jp_score_p',
        // ];
        // foreach ($scoreData['raw_scores'] as $rawKey => $value) {
        //     if (array_key_exists($rawKey, $mapping)) {
        //         $data[$mapping[$rawKey]] = $value;
        //     }
        // }
        // Untuk saat ini, saya akan mengasumsikan 'detailed_scores' adalah sumber yang benar berdasarkan output 'scoreMbti'.
    }
        
        UserMbtiScore::create($data);
    }
}