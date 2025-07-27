<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use App\Models\JobPosition;
use App\Models\User;
use App\Models\Test;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CreateAnalysisForm extends Component
{
    public $name;
    public $description;
    public $job_position_id;
    public $selected_candidates = [];

    public $jobPositions = [];
    public $availableCandidates = [];
    
    public $showCandidateList = false;

    public $debugInfo = '';
    public $totalTests = 3;

    protected $rules = [
        'name' => 'required|string|max:255',
        'job_position_id' => 'required|exists:job_positions,id',
        'selected_candidates' => 'required|array|min:2',
        'selected_candidates.*' => 'exists:users,id',
        'description' => 'nullable|string',
    ];

    protected $messages = [
        'name.required' => 'Nama analisis wajib diisi.',
        'job_position_id.required' => 'Posisi jabatan wajib dipilih.',
        'selected_candidates.required' => 'Harap pilih setidaknya dua kandidat untuk dibandingkan.',
        'selected_candidates.min' => 'Harap pilih setidaknya dua kandidat untuk dibandingkan.',
    ];

    /**
     * 
     * 
     */
    public function mount()
    {
        $this->jobPositions = JobPosition::orderBy('name', 'asc')->get();
        
        $this->availableCandidates = collect();
        $this->selected_candidates = [];
        
        Log::info('CreateAnalysisForm mounted', [
            'job_positions_count' => $this->jobPositions->count()
        ]);
    }

    /**
     * 
     */
    public function saveAnalysis()
    {
        $this->validate();

        try {
            $analysis = AnpAnalysis::create([
                'name' => $this->name,
                'job_position_id' => $this->job_position_id,
                'anp_network_structure_id' => null, 
                'hr_user_id' => Auth::id(),
                'status' => 'network_pending', 
                'description' => $this->description,
            ]);

            $analysis->candidates()->sync($this->selected_candidates);

            session()->put('current_anp_analysis_id', $analysis->id);

            Log::info('ANP Analysis created successfully', [
                'analysis_id' => $analysis->id,
                'analysis_name' => $analysis->name,
                'candidates_count' => count($this->selected_candidates),
                'job_position' => $this->jobPositions->find($this->job_position_id)->name ?? 'Unknown'
            ]);

            session()->flash('message', 'Analisis ANP berhasil dibuat. Silakan definisikan struktur jaringan.');

            return redirect()->route('h-r.anp.analysis.network.define', $analysis->id);

        } catch (\Exception $e) {
            Log::error('Failed to create ANP analysis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Gagal membuat analisis: ' . $e->getMessage());
        }
    }
    
    /**
     * 
     */
    public function updatedJobPositionId($value)
    {
        Log::info('Job position selection changed', ['job_position_id' => $value]);
        
        if ($value) {
            $this->availableCandidates = collect();
            $this->selected_candidates = [];
            $this->debugInfo = '';
            
            $programmingTestId = config('tests.types.programming.id', 1);
            $minimumScore = config('tests.types.programming.minimum_passing_score', 80);
            
            $candidates = User::where('role', User::ROLE_CANDIDATE)
                ->where('job_position_id', $value)
                ->with(['testProgress' => function($query) {
                    $query->where('status', 'completed');
                }])
                ->whereHas('testProgress', function($query) use ($programmingTestId, $minimumScore) {
                    $query->where('test_id', $programmingTestId)
                        ->where('status', 'completed')
                        ->where('score', '>=', $minimumScore);
                })
                ->get();
            $this->availableCandidates = $candidates->filter(function ($user) use ($programmingTestId) {
                $completedTestsCount = $user->testProgress
                    ->where('status', 'completed')
                    ->pluck('test_id')
                    ->unique()
                    ->count();
                
                $programmingScore = $user->testProgress
                    ->where('test_id', $programmingTestId)
                    ->first()
                    ->score ?? 0;
                
                Log::debug('Evaluating candidate', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'completed_tests' => $completedTestsCount,
                    'required_tests' => $this->totalTests,
                    'programming_score' => $programmingScore
                ]);
                
                return $completedTestsCount >= $this->totalTests;
            });
            
            $this->showCandidateList = true;
            
            Log::info('Candidates loaded for position', [
                'position_id' => $value,
                'eligible_candidates' => $this->availableCandidates->count()
            ]);
            
        } else {
            $this->showCandidateList = false;
            $this->availableCandidates = collect();
            $this->selected_candidates = [];
            $this->debugInfo = '';
        }
    }

    public function submit()
    {
        $this->validate();
        
        DB::transaction(function() {
            $analysis = AnpAnalysis::create([
                'name' => $this->goalName,
                'job_position_id' => $this->selectedJobPosition,
                'hr_user_id' => auth()->id(),
                'status' => 'network_pending', 
                'description' => $this->description,
            ]);
            
            $analysis->candidates()->attach($this->selectedCandidates);
            
            session()->put('current_anp_analysis_id', $analysis->id);
            
            return redirect()->route('h-r.anp.analysis.network-builder');
        });
    }

    /**
     * 
     */
    public function toggleAllCandidates()
    {
        if (count($this->selected_candidates) === $this->availableCandidates->count()) {
            $this->selected_candidates = [];
        } else {
            $this->selected_candidates = $this->availableCandidates->pluck('id')->toArray();
        }
    }

    /**
     * 
     */
    public function getSelectedCountProperty()
    {
        return count($this->selected_candidates);
    }

    /**Check if form is valid
     */
    public function getCanSubmitProperty()
    {
        return !empty($this->name) && 
               !empty($this->job_position_id) && 
               count($this->selected_candidates) >= 2;
    }

    /**
     * 
     */
    public function render()
    {
        return view('livewire.h-r.anp.create-analysis-form');
    }
}