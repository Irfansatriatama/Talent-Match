<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use App\Models\AnpCriteriaComparison;
use App\Models\AnpElement;
use App\Models\AnpCluster;
use App\Models\AnpDependency;
use App\Services\AnpCalculationService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class PairwiseCriteriaMatrix extends Component
{
    public $analysisId;
    public $analysis;
    public $controlCriterionContext;
    public $controlCriterionObject = null;
    public $elementsToCompare = [];
    public $elementTypeToCompare;
    
    public $comparisons = [];
    public $priorityVector = [];
    public $consistencyRatio = null;
    public $isConsistent = null;
    public $calculationResult = null;
    
    protected $rules = [
        'comparisons.*.*.value' => 'required|numeric|min:0.11|max:9'
    ];
    
    protected $messages = [
        'comparisons.*.*.value.required' => 'Semua nilai perbandingan harus diisi.',
        'comparisons.*.*.value.numeric' => 'Nilai perbandingan harus angka.',
        'comparisons.*.*.value.min' => 'Nilai perbandingan minimal 1/9 (sekitar 0.11).',
        'comparisons.*.*.value.max' => 'Nilai perbandingan maksimal 9.',
    ];

    public function mount()
    {
        $this->analysisId = session('current_anp_analysis_id');
        $pairwiseContext = session('anp_pairwise_context');

        if (!$this->analysisId || !$pairwiseContext) {
            session()->flash('error', 'Sesi perbandingan tidak valid. Harap mulai proses dari awal.');
            return redirect()->route('h-r.anp.analysis.index');
        }

        $this->analysis = AnpAnalysis::with('networkStructure')->findOrFail($this->analysisId);
        $this->controlCriterionContext = $pairwiseContext['control_criterion_context_type'];
        $controlCriterionId = $pairwiseContext['control_criterion_context_id'];

        if ($this->controlCriterionContext === 'element' && $controlCriterionId) {
            $this->controlCriterionObject = AnpElement::find($controlCriterionId);
        } elseif ($this->controlCriterionContext === 'cluster' && $controlCriterionId) {
            $this->controlCriterionObject = AnpCluster::find($controlCriterionId);
        }

        $this->determineElementsToCompare();
        
        // Load dari session dengan key yang unik
        $sessionKey = 'pairwise_criteria_comparisons_' . $this->analysisId . '_' . $this->controlCriterionContext . '_' . $controlCriterionId;
        $savedComparisons = session($sessionKey, []);
        
        if (!empty($savedComparisons)) {
            $this->comparisons = $savedComparisons;
            $this->loadCalculationResults();
        } else {
            $this->initializeComparisons();
        }
        
        $this->loadExistingComparisons();
    }

    protected function determineElementsToCompare()
    {
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
    }

    public function initializeComparisons()
    {
        $elementCount = count($this->elementsToCompare);
        
        for ($i = 0; $i < $elementCount; $i++) {
            for ($j = 0; $j < $elementCount; $j++) {
                if (!isset($this->comparisons[$i][$j])) {
                    if ($i == $j) {
                        $this->comparisons[$i][$j]['value'] = 1;
                    } else {
                        $this->comparisons[$i][$j]['value'] = 1;
                    }
                }
            }
        }
    }

    public function updated($propertyName)
    {
        // Auto-calculate ketika nilai berubah
        if (str_starts_with($propertyName, 'comparisons.')) {
            preg_match('/comparisons\.(\d+)\.(\d+)\.value/', $propertyName, $matches);
            if (count($matches) === 3) {
                $i = (int)$matches[1];
                $j = (int)$matches[2];
                
                // Update nilai reciprocal
                if (isset($this->comparisons[$i][$j]['value'])) {
                    $value = (float)$this->comparisons[$i][$j]['value'];
                    
                    // Validasi nilai
                    if ($value < 0.11 || $value > 9) {
                        $this->comparisons[$i][$j]['value'] = 1;
                        $this->addError("comparisons.{$i}.{$j}.value", 'Nilai harus antara 0.11 (1/9) dan 9');
                        return;
                    }
                    
                    $this->resetErrorBag("comparisons.{$i}.{$j}.value");
                    $this->resetErrorBag("comparisons.{$j}.{$i}.value");
                    
                    if ($value > 0 && $i !== $j) {
                        $this->comparisons[$j][$i]['value'] = round(1 / $value, 4);
                    }
                }
                
                // Save to session dengan key yang unik
                $controlCriterionId = $this->controlCriterionObject ? $this->controlCriterionObject->id : 'goal';
                $sessionKey = 'pairwise_criteria_comparisons_' . $this->analysisId . '_' . $this->controlCriterionContext . '_' . $controlCriterionId;
                session([$sessionKey => $this->comparisons]);
                
                // Reset calculation results
                $this->consistencyRatio = null;
                $this->isConsistent = null;
                $this->priorityVector = [];
                $this->calculationResult = null;
            }
        }
    }

    public function loadExistingComparisons()
    {
        $comparison = AnpCriteriaComparison::where('anp_analysis_id', $this->analysis->id)
            ->where('control_criterionable_type', $this->controlCriterionObject ? get_class($this->controlCriterionObject) : null)
            ->where('control_criterionable_id', $this->controlCriterionObject ? $this->controlCriterionObject->id : null)
            ->where('compared_elements_type', $this->elementTypeToCompare)
            ->first();

        if ($comparison) {
            // Convert matrix values to comparison format
            $matrixValues = $comparison->comparison_data['matrix_values'] ?? [];
            $elementIds = collect($this->elementsToCompare)->pluck('id')->values()->all();
            
            foreach ($elementIds as $i => $rowId) {
                foreach ($elementIds as $j => $colId) {
                    if (isset($matrixValues[$rowId][$colId])) {
                        $this->comparisons[$i][$j]['value'] = (float)$matrixValues[$rowId][$colId];
                    }
                }
            }
            
            $this->priorityVector = $comparison->priority_vector ?? [];
            
            if ($comparison->consistency) {
                $this->consistencyRatio = $comparison->consistency->consistency_ratio;
                $this->isConsistent = $comparison->consistency->is_consistent;
            }
        }
    }

    public function loadCalculationResults()
    {
        // Load calculation results from session if available
        $controlCriterionId = $this->controlCriterionObject ? $this->controlCriterionObject->id : 'goal';
        $sessionKey = 'pairwise_criteria_results_' . $this->analysisId . '_' . $this->controlCriterionContext . '_' . $controlCriterionId;
        $savedResults = session($sessionKey, []);
        
        if (!empty($savedResults)) {
            $this->priorityVector = $savedResults['priority_vector'] ?? [];
            $this->consistencyRatio = $savedResults['consistency_ratio'] ?? null;
            $this->isConsistent = $savedResults['is_consistent'] ?? null;
            $this->calculationResult = $savedResults['calculation_result'] ?? null;
        }
    }

    protected function validateMatrix(): bool
    {
        $hasEmptyCells = false;
        $emptyCount = 0;
        $elementCount = count($this->elementsToCompare);
        
        for ($i = 0; $i < $elementCount; $i++) {
            for ($j = 0; $j < $elementCount; $j++) {
                if ($i !== $j) {
                    $value = $this->comparisons[$i][$j]['value'] ?? null;
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
                'comparisons.*.*.value' => 'required|numeric|min:0.11|max:9'
            ], [
                'comparisons.*.*.value.required' => 'Nilai ini wajib diisi.',
                'comparisons.*.*.value.numeric' => 'Nilai harus berupa angka.',
                'comparisons.*.*.value.min' => 'Nilai minimal adalah 1/9.',
                'comparisons.*.*.value.max' => 'Nilai maksimal adalah 9.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isConsistent = false;
            $this->dispatch('notify', ['message' => 'Validasi gagal. Harap isi semua nilai perbandingan dengan benar.', 'type' => 'error']);
            throw $e;
        }

        $service = new AnpCalculationService();
        $matrixForCalc = [];
        $elementIds = collect($this->elementsToCompare)->pluck('id')->values()->all();
        
        foreach ($elementIds as $i => $rowId) {
            foreach ($elementIds as $j => $colId) {
                if (isset($this->comparisons[$i][$j]['value'])) {
                    $matrixForCalc[$rowId][$colId] = (float)$this->comparisons[$i][$j]['value'];
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
            
            // Save results to session
            $controlCriterionId = $this->controlCriterionObject ? $this->controlCriterionObject->id : 'goal';
            $sessionKey = 'pairwise_criteria_results_' . $this->analysisId . '_' . $this->controlCriterionContext . '_' . $controlCriterionId;
            session([$sessionKey => [
                'priority_vector' => $this->priorityVector,
                'consistency_ratio' => $this->consistencyRatio,
                'is_consistent' => $this->isConsistent,
                'calculation_result' => $this->calculationResult
            ]]);
            
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
            $this->dispatch('notify', ['message' => 'Gagal menyimpan. Matriks perbandingan tidak konsisten (CR > ' . config('anp.consistency_ratio_threshold', 0.10) . '). Harap perbaiki.', 'type' => 'error']);
            return;
        }

        // Convert comparisons to matrix format for database
        $comparisonDataForDb = [];
        $elementIds = collect($this->elementsToCompare)->pluck('id')->values()->all();
        
        foreach ($elementIds as $i => $rowId) {
            foreach ($elementIds as $j => $colId) {
                $comparisonDataForDb[$rowId][$colId] = (float)$this->comparisons[$i][$j]['value'];
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
                'comparison_data' => ['matrix_values' => $comparisonDataForDb, 'element_ids' => $elementIds],
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

        // Clear session untuk comparison ini
        $controlCriterionId = $this->controlCriterionObject ? $this->controlCriterionObject->id : 'goal';
        $sessionKey = 'pairwise_criteria_comparisons_' . $this->analysisId . '_' . $this->controlCriterionContext . '_' . $controlCriterionId;
        session()->forget($sessionKey);
        
        $resultsSessionKey = 'pairwise_criteria_results_' . $this->analysisId . '_' . $this->controlCriterionContext . '_' . $controlCriterionId;
        session()->forget($resultsSessionKey);

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

                return redirect()->route('h-r.anp.analysis.pairwise-criteria');
            }
        }

        $allDependencies = $analysis->networkStructure->dependencies;
        $completedInterdependencyComps = $analysis->interdependencyComparisons()->pluck('anp_dependency_id');

        foreach ($allDependencies as $dependency) {
            if (!$completedInterdependencyComps->contains($dependency->id)) {
                return redirect()->route('h-r.anp.analysis.interdependency.pairwise.form', [
                    'anpAnalysis' => $analysis->id,
                    'anpDependency' => $dependency->id
                ]);
            }
        }

        $allCriteriaElements = $analysis->networkStructure->elements;
        $completedAlternativeComps = $analysis->alternativeComparisons()->pluck('anp_element_id');

        foreach ($allCriteriaElements as $element) {
            if (!$completedAlternativeComps->contains($element->id)) {
                return redirect()->route('h-r.anp.analysis.alternative.pairwise.form', [
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
        return view('livewire.h-r.anp.pairwise-criteria-matrix');
    }
}