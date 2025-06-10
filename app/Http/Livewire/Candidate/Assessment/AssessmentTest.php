<?php

namespace App\Http\Livewire\Candidate\Assessment;

namespace App\Http\Livewire\Candidate\Assessment;

use App\Models\Test;
use App\Models\UserTestProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Collection;

class AssessmentTest extends Component
{
    public Collection $tests;
    public array $testStatuses = [];

    public function mount()
    {
        $user = Auth::user();
        $allTests = Test::orderBy('test_order')->get(); 
        
        if ($allTests->isEmpty()) {
            $this->tests = collect();
            $this->testStatuses = [];
            session()->flash('info', 'Saat ini belum ada tes yang tersedia.');
            return;
        }
        
        $this->tests = $allTests;
        $tempTestProgress = []; 

        $userProgressRecords = UserTestProgress::where('user_id', $user->id)
                                ->whereIn('test_id', $allTests->pluck('test_id'))
                                ->get()
                                ->keyBy('test_id'); 

        foreach ($this->tests as $test) {
            $progress = $userProgressRecords->get($test->test_id);
            $status = $progress ? $progress->status : 'not_started'; 
            
            $this->testStatuses[$test->test_id] = [
                'test_id' => $test->test_id,
                'test_name' => $test->test_name, 
                'description' => $test->description, 
                'time_limit_minutes' => $test->time_limit_minutes, 
                'test_order' => $test->test_order, 
                'progress' => $progress, 
                'status' => $status,
                'route' => $this->getTestRoute($test->test_type), 
                'can_start' => false 
            ];
        }

        foreach ($this->tests as $test) {
            $currentTestInfo = &$this->testStatuses[$test->test_id]; 

            if ($currentTestInfo['status'] === 'in_progress') { 
                $currentTestInfo['can_start'] = true; 
            } elseif ($currentTestInfo['status'] === 'not_started') { 
                if ($currentTestInfo['test_order'] == 1) {
                    $currentTestInfo['can_start'] = true;
                } else {
                    $previousTestOrder = $currentTestInfo['test_order'] - 1;
                    $previousTest = $this->tests->firstWhere('test_order', $previousTestOrder);

                    if ($previousTest && isset($this->testStatuses[$previousTest->test_id])) {
                        if ($this->testStatuses[$previousTest->test_id]['status'] === 'completed') { 
                            $currentTestInfo['can_start'] = true;
                        }
                    }
                }
            }
        }
        unset($currentTestInfo);
    }

    private function getTestRoute($testType)
    {
        switch ($testType) { 
            case 'programming':
                return route('candidate.assessment.programming'); 
            case 'riasec':
                return route('candidate.assessment.riasec'); 
            case 'mbti':
                return route('candidate.assessment.mbti'); 
            default:
                return '#';
        }
    }

    public function render()
    {
        return view('livewire.candidate.assessment.assessment-test', [
            'test_statuses_list' => array_values($this->testStatuses)
        ])->layout('layouts.app'); 
    }
}