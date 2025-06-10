<?php

namespace App\Http\Livewire\HR;

use App\Models\User;
use App\Models\Test;
use Livewire\Component;

class DetailCandidate extends Component
{
    public User $candidate;
    public array $testResults = [];
    public int $totalTests;

    public function mount(User $candidate)
    {
        if ($candidate->role !== 'candidate') {
            abort(404);
        }

        $this->candidate = $candidate->load([
            'testProgress.test', 
            'latestMbtiScore',
            'jobPosition'
        ]);
        
        $this->totalTests = Test::count() ?: 3;
        $this->prepareTestResults();
    }

    public function prepareTestResults(): void
    {
        $tests = Test::orderBy('test_order')->get();
        $results = [];
        
        $allProgress = $this->candidate->testProgress->keyBy('test_id');
        
        foreach ($tests as $test) {
            $progress = $allProgress->get($test->test_id);
            
            if ($progress) {
                $summary = $progress->result_summary;
                
                if ($test->test_type === 'mbti' && $progress->status === 'completed') {
                    if (!$this->candidate->relationLoaded('latestMbtiScore')) {
                        $this->candidate->load('latestMbtiScore');
                    }
                    $summary = $this->candidate->latestMbtiScore?->mbti_type ?? $progress->result_summary;
                }
                
                $results[$test->test_name] = [
                    'status' => ucfirst($progress->status),
                    'score' => $progress->score,
                    'summary' => $summary,
                    'completed_at' => $progress->completed_at?->format('d M Y H:i'),
                    'time_spent' => $progress->time_spent_seconds ? floor($progress->time_spent_seconds / 60) . ' menit' : null
                ];
            } else {
                $results[$test->test_name] = [
                    'status' => 'Belum Mulai',
                    'score' => null,
                    'summary' => null,
                    'completed_at' => null,
                    'time_spent' => null
                ];
            }
        }
        
        $this->testResults = $results;
    }

    public function render()
    {
        return view('livewire.hr.detail-candidate', [
            'pageTitle' => 'Detail Kandidat'
        ]);
    }
}