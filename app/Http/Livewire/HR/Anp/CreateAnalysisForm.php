<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use App\Models\JobPosition;
use App\Models\User;
use App\Models\Test;
use App\Models\AnpNetworkStructure;
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

    public function mount()
    {
        $this->jobPositions = JobPosition::orderBy('name')->get();
        $this->availableCandidates = collect();
        
        $this->totalTests = Test::count() ?: 3;
        
        Log::info('CreateAnalysisForm mounted', [
            'total_tests' => $this->totalTests,
            'job_positions_count' => $this->jobPositions->count()
        ]);
    }
    
    public function updatedJobPositionId($value)
    {
        Log::info('Job position updated', ['value' => $value]);
        
        if ($value) {
            $this->availableCandidates = collect();
            $this->selected_candidates = [];
            $this->debugInfo = '';
            
            // Menggunakan config untuk test ID dan minimum score
            $programmingTestId = config('tests.types.programming.id', 1);
            $minimumScore = config('tests.types.programming.minimum_passing_score', 80);
            
            // Query dengan filter tambahan untuk skor programming
            $completedCandidatesQuery = User::where('role', User::ROLE_CANDIDATE)
                ->where('job_position_id', $value)
                ->with(['testProgress' => function($query) {
                    $query->where('status', 'completed');
                }])
                ->whereHas('testProgress', function($query) use ($programmingTestId, $minimumScore) {
                    // Filter untuk tes programming dengan skor minimal dari config
                    $query->where('test_id', $programmingTestId)
                        ->where('status', 'completed')
                        ->where('score', '>=', $minimumScore);
                })
                ->get()
                ->filter(function ($user) {
                    $completedTestsCount = $user->testProgress
                        ->where('status', 'completed')
                        ->pluck('test_id')
                        ->unique()
                        ->count();
                    
                    Log::info('Checking candidate', [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'completed_tests' => $completedTestsCount,
                        'required_tests' => $this->totalTests,
                        'programming_score' => $user->testProgress->where('test_id', config('tests.types.programming.id', 1))->first()->score ?? 'N/A'
                    ]);
                    
                    return $completedTestsCount >= $this->totalTests;
                });
            
            $this->availableCandidates = $completedCandidatesQuery;
            $this->showCandidateList = true;
            
        } else {
            $this->showCandidateList = false;
            $this->availableCandidates = collect();
            $this->selected_candidates = [];
            $this->debugInfo = '';
        }
    }

    public function saveAnalysis()
    {
        $this->validate();

        try {
            $defaultStructure = AnpNetworkStructure::first();

            if (!$defaultStructure) {
                $defaultStructure = AnpNetworkStructure::create([
                    'name' => 'Default ANP Structure',
                    'description' => 'Struktur jaringan default untuk analisis ANP'
                ]);
                
                Log::info('Created default ANP network structure', ['id' => $defaultStructure->id]);
            }

            $analysis = AnpAnalysis::create([
                'name' => $this->name,
                'job_position_id' => $this->job_position_id,
                'anp_network_structure_id' => $defaultStructure->id,
                'hr_user_id' => Auth::id(),
                'status' => 'network_pending', 
                'description' => $this->description,
            ]);

            $analysis->candidates()->sync($this->selected_candidates);

            session()->put('current_anp_analysis_id', $analysis->id);

            Log::info('ANP Analysis created successfully', [
                'analysis_id' => $analysis->id,
                'candidates_count' => count($this->selected_candidates)
            ]);

            session()->flash('message', 'Analisis ANP baru berhasil dibuat. Silakan lanjutkan ke definisi jaringan.');

            return redirect()->route('h-r.anp.analysis.network.define', $analysis->id);

        } catch (\Exception $e) {
            Log::error('Failed to create ANP analysis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Gagal membuat analisis ANP: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.h-r.anp.create-analysis-form');
    }
}