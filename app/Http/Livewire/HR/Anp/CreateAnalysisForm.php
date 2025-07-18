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

    // 1. Update CreateAnalysisForm.php
    public function saveAnalysis()
    {
        $this->validate();

        try {
            // CRITICAL: Don't use default/shared structure!
            // Each analysis MUST have its own structure
            
            $analysis = AnpAnalysis::create([
                'name' => $this->name,
                'job_position_id' => $this->job_position_id,
                'anp_network_structure_id' => null, // Start with NULL
                'hr_user_id' => Auth::id(),
                'status' => 'network_pending', 
                'description' => $this->description,
            ]);

            $analysis->candidates()->sync($this->selected_candidates);

            session()->put('current_anp_analysis_id', $analysis->id);

            Log::info('ANP Analysis created successfully', [
                'analysis_id' => $analysis->id,
                'candidates_count' => count($this->selected_candidates),
                'network_structure_id' => 'Will be created on network definition'
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

    // 2. Update NetworkBuilder mount() method
    public function mount(AnpAnalysis $anpAnalysis = null)
    {
        if ($anpAnalysis) {
            $this->analysis = $anpAnalysis;
            session()->put('current_anp_analysis_id', $anpAnalysis->id);
        } else {
            $analysisId = session('current_anp_analysis_id');
            if (!$analysisId) {
                return redirect()->route('h-r.anp.analysis.index');
            }
            $this->analysis = AnpAnalysis::findOrFail($analysisId);
        }
        
        $this->initializeNetworkStructure();
        $this->loadNetworkData();
    }

    // 3. Fix initializeNetworkStructure() to properly handle new analysis
    private function initializeNetworkStructure()
    {
        $this->resetFormFields();
        
        if (!$this->analysis->anp_network_structure_id) {
            // Create NEW unique structure for this analysis
            $this->networkStructure = AnpNetworkStructure::create([
                'name' => 'Network untuk ' . $this->analysis->name . ' (ID: ' . $this->analysis->id . ')',
                'description' => 'Struktur jaringan unik untuk analisis ' . $this->analysis->name,
                'is_frozen' => false,
                'version' => 1
            ]);
            
            $this->analysis->anp_network_structure_id = $this->networkStructure->id;
            $this->analysis->save();
            
            // Copy template structure if exists
            $this->copyTemplateStructure();
            
            Log::info('Created new network structure', [
                'analysis_id' => $this->analysis->id,
                'structure_id' => $this->networkStructure->id
            ]);
        } else {
            // Load existing structure
            $this->networkStructure = AnpNetworkStructure::find($this->analysis->anp_network_structure_id);
            
            // CRITICAL: Check if this is a reused structure from another analysis
            $otherAnalysesCount = AnpAnalysis::where('anp_network_structure_id', $this->networkStructure->id)
                ->where('id', '!=', $this->analysis->id)
                ->count();
                
            if ($otherAnalysesCount > 0) {
                Log::warning('Network structure is shared with other analyses!', [
                    'structure_id' => $this->networkStructure->id,
                    'other_analyses_count' => $otherAnalysesCount
                ]);
                
                // Force create new structure
                $this->createNewStructureForAnalysis();
            }
        }
    }

    // 4. Add method to force new structure creation
    private function createNewStructureForAnalysis()
    {
        $oldStructure = $this->networkStructure;
        
        // Create new structure
        $this->networkStructure = AnpNetworkStructure::create([
            'name' => 'Network untuk ' . $this->analysis->name . ' (ID: ' . $this->analysis->id . ')',
            'description' => 'Struktur jaringan unik untuk analisis ' . $this->analysis->name,
            'is_frozen' => false,
            'version' => 1,
            'parent_structure_id' => $oldStructure->id
        ]);
        
        // Update analysis
        $this->analysis->anp_network_structure_id = $this->networkStructure->id;
        $this->analysis->save();
        
        // Copy existing data if needed
        if ($oldStructure && !$oldStructure->is_frozen) {
            $this->copyStructureData($oldStructure, $this->networkStructure);
        }
        
        Log::info('Created new structure to replace shared structure', [
            'analysis_id' => $this->analysis->id,
            'old_structure_id' => $oldStructure->id,
            'new_structure_id' => $this->networkStructure->id
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

    // 5. Update the view to show freeze state clearly
    public function render()
    {
        return view('livewire.h-r.anp.network-builder', [
            'dependencies' => $this->networkStructure ? 
                $this->networkStructure->dependencies()
                    ->whereNull('deleted_at')
                    ->with(['sourceable', 'targetable'])
                    ->get() : 
                collect(),
            'isStructureFrozen' => $this->networkStructure ? $this->networkStructure->is_frozen : false,
            'analysisName' => $this->analysis->name,
            'structureId' => $this->networkStructure ? $this->networkStructure->id : null,
        ]);
    }
}