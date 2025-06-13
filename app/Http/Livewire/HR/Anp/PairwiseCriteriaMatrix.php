<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use App\Models\AnpCriteriaComparison;
use App\Models\AnpElement;
use App\Models\AnpCluster;
use App\Services\AnpCalculationService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;


class PairwiseCriteriaMatrix extends Component
{
    public AnpAnalysis $analysis;
    public $controlCriterionContext; 
    public $controlCriterionObject = null; 

    public $elementsToCompare = [];
    public $elementTypeToCompare; 

    public $matrixValues = []; 
    public $priorityVector = [];
    public $consistencyRatio = null;
    public $isConsistent = null;
    public $calculationResult = null;

    protected $rules = [
        'matrixValues' => 'required|array',
        'matrixValues.*.*' => 'required|numeric|min:0.11|max:9', 
    ];
     protected $messages = [
        'matrixValues.*.*.required' => 'Semua nilai perbandingan harus diisi.',
        'matrixValues.*.*.numeric' => 'Nilai perbandingan harus angka.',
        'matrixValues.*.*.min' => 'Nilai perbandingan minimal 1/9 (sekitar 0.11).',
        'matrixValues.*.*.max' => 'Nilai perbandingan maksimal 9.',
    ];


    public function mount()
    {
        $analysisId = session('current_anp_analysis_id');
        $pairwiseContext = session('anp_pairwise_context');

        if (!$analysisId || !$pairwiseContext) {
            session()->flash('error', 'Sesi perbandingan tidak valid. Harap mulai proses dari awal.');
            return redirect()->route('hr.anp.analysis.index');
        }

        $this->analysis = AnpAnalysis::with('networkStructure')->findOrFail($analysisId);
        $this->controlCriterionContext = $pairwiseContext['control_criterion_context_type'];
        $controlCriterionId = $pairwiseContext['control_criterion_context_id'];

        if ($this->controlCriterionContext === 'element' && $controlCriterionId) {
            $this->controlCriterionObject = AnpElement::find($controlCriterionId);
        } elseif ($this->controlCriterionContext === 'cluster' && $controlCriterionId) {
            $this->controlCriterionObject = AnpCluster::find($controlCriterionId);
        }
        if ($this->controlCriterionContext === 'goal') {
            $clusters = $this->analysis->networkStructure->clusters;
            if ($clusters->isNotEmpty()) {
                $this->elementsToCompare = $clusters->all();
                $this->elementTypeToCompare = AnpCluster::class;
            } else {
                $this->elementsToCompare = $this->analysis->networkStructure->elements()->whereNull('anp_cluster_id')->get()->all();
                $this->elementTypeToCompare = AnpElement::class;
            }
        } elseif ($this->controlCriterionObject instanceof AnpCluster) {
            $this->elementsToCompare = $this->controlCriterionObject->elements()->get()->all();
            $this->elementTypeToCompare = AnpElement::class;
        } elseif ($this->controlCriterionObject instanceof AnpElement) {
            $dependencies = AnpDependency::where('anp_network_structure_id', $this->analysis->anp_network_structure_id)
                ->where('targetable_id', $this->controlCriterionObject->id)
                ->where('targetable_type', AnpElement::class)
                ->with('sourceable') 
                ->get();
                
            $this->elementsToCompare = $dependencies->pluck('sourceable')->unique()->values()->all();
            $this->elementTypeToCompare = AnpElement::class;
        }

        $this->initializeMatrix();
        $this->loadExistingComparison();
    }

    public function initializeMatrix()
    {
        $this->matrixValues = [];
        foreach ($this->elementsToCompare as $rowElement) {
            foreach ($this->elementsToCompare as $colElement) {
                if ($rowElement->id == $colElement->id) {
                    $this->matrixValues[$rowElement->id][$colElement->id] = 1;
                } else {
                    $this->matrixValues[$rowElement->id][$colElement->id] = null;
                }
            }
        }
    }
    
    public function loadExistingComparison()
    {
        $comparison = AnpCriteriaComparison::where('anp_analysis_id', $this->analysis->id)
            ->where('control_criterionable_type', $this->controlCriterionObject ? get_class($this->controlCriterionObject) : null)
            ->where('control_criterionable_id', $this->controlCriterionObject ? $this->controlCriterionObject->id : null)
            ->where('compared_elements_type', $this->elementTypeToCompare)
            ->first();

        if ($comparison) {
            $this->matrixValues = $comparison->comparison_data['matrix_values'] ?? $this->matrixValues; 
            $this->priorityVector = $comparison->priority_vector ?? [];
            if ($comparison->consistency) {
                $this->consistencyRatio = $comparison->consistency->consistency_ratio;
                $this->isConsistent = $comparison->consistency->is_consistent;
            }
        }
    }

    public function updatedMatrixValues($value, $key)
    {
        [$rowId, $colId] = explode('.', $key);
        
        if ($rowId == $colId) {
            $this->matrixValues[$rowId][$colId] = 1;
            return;
        }
        
        $floatValue = (float) $value;
        
        if ($floatValue < 0.11 || $floatValue > 9) {
            if (!isset($this->matrixValues[$rowId][$colId])) {
                $this->matrixValues[$rowId][$colId] = 1;
            }
            $this->addError("matrixValues.{$rowId}.{$colId}", 'Nilai harus antara 0.11 (1/9) dan 9');
            return;
        }
        
        $this->resetErrorBag("matrixValues.{$rowId}.{$colId}");
        $this->resetErrorBag("matrixValues.{$colId}.{$rowId}");
        
        $this->matrixValues[$rowId][$colId] = $floatValue;
        
        if ($floatValue > 0) {
            $reciprocalValue = round(1 / $floatValue, 4);
            $this->matrixValues[$colId][$rowId] = $reciprocalValue;
        }
        $this->consistencyRatio = null;
        $this->isConsistent = null;
        $this->priorityVector = [];
        $this->calculationResult = null;
        
    }

    protected function validateMatrix(): bool
    {
        $hasEmptyCells = false;
        $emptyCount = 0;
        
        foreach ($this->elementsToCompare as $rowElement) {
            foreach ($this->elementsToCompare as $colElement) {
                if ($rowElement->id !== $colElement->id) {
                    $value = $this->matrixValues[$rowElement->id][$colElement->id] ?? null;
                    if ($value === null || $value === '' || !is_numeric($value)) {
                        $hasEmptyCells = true;
                        $emptyCount++;
                    }
                }
            }
        }
        
        if ($hasEmptyCells) {
            $this->dispatch('notify', [
                'message' => "Perhatian: Ada {$emptyCount} sel yang belum diisi. Harap lengkapi semua nilai perbandingan.",
                'type' => 'warning'
            ]);
            return false;
        }
        
        return true;
    }


    public function recalculateConsistency()
    {
        try {
            $this->validate([
                'matrixValues.*.*' => 'required|numeric|min:0.11|max:9'
            ], [
                'matrixValues.*.*.required' => 'Nilai ini wajib diisi.',
                'matrixValues.*.*.numeric' => 'Nilai harus berupa angka.',
                'matrixValues.*.*.min' => 'Nilai minimal adalah 1/9.',
                'matrixValues.*.*.max' => 'Nilai maksimal adalah 9.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isConsistent = false;
            $this->dispatch('notify', ['message' => 'Validasi gagal. Harap isi semua nilai perbandingan dengan benar.', 'type' => 'error']);
            throw $e;
        }

        $service = new \App\Services\AnpCalculationService();
        $matrixForCalc = [];
        
        foreach ($this->alternativesToCompare as $rowAlt) {
            foreach ($this->alternativesToCompare as $colAlt) {
                if (isset($this->matrixValues[$rowAlt->id][$colAlt->id])) {
                    $matrixForCalc[$rowAlt->id][$colAlt->id] = (float) $this->matrixValues[$rowAlt->id][$colAlt->id];
                }
            }
        }

        $this->calculationResult = $service->calculateEigenvectorAndCR($matrixForCalc);

        if (isset($this->calculationResult['error'])) {
            $this->dispatch('notify', ['message' => 'Error: ' . $this->calculationResult['error'], 'type' => 'error']);
        } else {
            $this->priorityVector = $this->calculationResult['priority_vector'];
            $this->consistencyRatio = $this->calculationResult['consistency_ratio'];
            $this->isConsistent = $this->calculationResult['is_consistent'];
            $message = 'Kalkulasi selesai. CR: ' . round($this->consistencyRatio, 4) . ($this->isConsistent ? ' (Konsisten)' : ' (Tidak Konsisten!)');
            $this->dispatch('notify', ['message' => $message, 'type' => $this->isConsistent ? 'success' : 'error']);
        }
    }

    public function saveComparisons()
    {
        if (!$this->validateMatrix()) {
            return;
        }    

        $this->recalculateConsistency(); 

        if (is_null($this->isConsistent)) {
             $this->dispatch('notify', ['message' => 'Gagal menyimpan. Harap hitung konsistensi terlebih dahulu dan pastikan semua nilai terisi.', 'type' => 'error']);
            return;
        }
        
        if (!$this->isConsistent) {
            $this->dispatch('notify', ['message' => 'Gagal menyimpan. Matriks perbandingan tidak konsisten (CR > '.config('anp.consistency_ratio_threshold', 0.10).'). Harap perbaiki.', 'type' => 'error']);
            return;
        }
        
        $comparisonDataForDb = [];
        foreach($this->elementsToCompare as $rowEl){
            foreach($this->elementsToCompare as $colEl){
                $comparisonDataForDb[$rowEl->id][$colEl->id] = (float) $this->matrixValues[$rowEl->id][$colEl->id];
            }
        }

        $comparison = AnpCriteriaComparison::updateOrCreate(
            [
                'anp_analysis_id' => $this->analysis->id,
                'control_criterionable_type' => $this->controlCriterionObject ? get_class($this->controlCriterionObject) : null,
                'control_criterionable_id' => $this->controlCriterionObject ? $this->controlCriterionObject->id : null,
                'compared_elements_type' => $this->elementTypeToCompare,
            ],
            [
                'comparison_data' => ['matrix_values' => $comparisonDataForDb, 'element_ids' => collect($this->elementsToCompare)->pluck('id')->all()],
                'priority_vector' => $this->priorityVector,
            ]
        );
        
        $comparison->consistency()->updateOrCreate(
            [],
            [
                'consistency_ratio' => $this->consistencyRatio,
                'is_consistent' => $this->isConsistent,
            ]
        );

        $this->dispatch('notify', ['message' => 'Perbandingan kriteria berhasil disimpan.', 'type' => 'success']);
        
    }

    public function saveAndContinue()
    {
        $this->saveComparisons();

        if (!$this->isConsistent) {
            $this->dispatchBrowserEvent('show-consistency-error');
            return;
        }

        $analysis = $this->analysis->load(['networkStructure.dependencies', 'networkStructure.elements', 'networkStructure.clusters']);

        $clustersWithMultipleElements = $analysis->networkStructure->clusters()
            ->withCount('elements')
            ->having('elements_count', '>', 1)
            ->get();
        
        $completedInnerComparisons = $analysis->criteriaComparisons()
            ->where('control_criterionable_type', AnpCluster::class)
            ->pluck('control_criterionable_id');
        
        foreach ($clustersWithMultipleElements as $cluster) {
            if (!$completedInnerComparisons->contains($cluster->id)) {
                session()->put('anp_pairwise_context', [
                    'control_criterion_context_type' => 'cluster',
                    'control_criterion_context_id' => $cluster->id,
                ]);
                
                Log::info("[ANP] Redirecting to inner dependence comparison for cluster: {$cluster->name}");
                
                return redirect()->route('hr.anp.analysis.pairwise-criteria');
            }
        }

        $allDependencies = $analysis->networkStructure->dependencies;
        $completedInterdependencyComps = $analysis->interdependencyComparisons()->pluck('anp_dependency_id');
        
        foreach ($allDependencies as $dependency) {
            if (!$completedInterdependencyComps->contains($dependency->id)) {
                return redirect()->route('hr.anp.analysis.interdependency.pairwise.form', [
                    'anpAnalysis' => $analysis->id,
                    'anpDependency' => $dependency->id
                ]);
            }
        }

        $allCriteriaElements = $analysis->networkStructure->elements;
        $completedAlternativeComps = $analysis->alternativeComparisons()->pluck('anp_element_id');

        foreach ($allCriteriaElements as $element) {
            if (!$completedAlternativeComps->contains($element->id)) {
                return redirect()->route('hr.anp.analysis.alternative.pairwise.form', [
                    'anpAnalysis' => $analysis->id,
                    'anpElement' => $element->id
                ]);
            }
        }
        
        $this->dispatch('notify', [
            'message' => 'Semua perbandingan telah selesai! Anda sekarang bisa memicu kalkulasi akhir.', 
            'type' => 'success'
        ]);
    }


    public function render()
    {
        return view('livewire.hr.anp.pairwise-criteria-matrix');
    }
}