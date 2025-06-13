<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use App\Models\AnpDependency;
use App\Models\AnpInterdependencyComparison;
use App\Models\AnpElement;
use App\Models\AnpCluster;
use App\Services\AnpCalculationService;
use Livewire\Component;

class PairwiseInterdependenciesMatrix extends Component
{
    public AnpAnalysis $analysis;
    public AnpDependency $dependency;

    public $sourceElementsToCompare = [];
    public $targetNodeDescription;

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

    public function mount(AnpAnalysis $anpAnalysis, AnpDependency $anpDependency)
    {
        $this->analysis = $anpAnalysis;
        $this->dependency = $anpDependency->load(['sourceable', 'targetable']);

        $this->targetNodeDescription = $this->dependency->targetable->name . ' (' . ($this->dependency->targetable_type == AnpElement::class ? 'Elemen' : 'Cluster') . ')';
        
        if ($this->dependency->sourceable_type == AnpCluster::class) {
            $sourceCluster = AnpCluster::with('elements')->find($this->dependency->sourceable_id);
            if ($sourceCluster) {
                $this->sourceElementsToCompare = $sourceCluster->elements->all();
            }
        } else if ($this->dependency->sourceable_type == AnpElement::class) {
            $siblingDependencies = AnpDependency::where('anp_network_structure_id', $this->analysis->anp_network_structure_id)
                ->where('targetable_id', $this->dependency->targetable_id)
                ->where('targetable_type', $this->dependency->targetable_type)
                ->where('sourceable_type', $this->dependency->sourceable_type)
                ->with('sourceable')
                ->get();

            if ($siblingDependencies->isNotEmpty()) {
                $this->sourceElementsToCompare = $siblingDependencies->pluck('sourceable')->unique('id')->all();
            }
        }

        $this->initializeMatrix();
        $this->loadExistingComparison();
    }

    public function initializeMatrix()
    {
        $this->matrixValues = [];
        foreach ($this->sourceElementsToCompare as $rowElement) {
            foreach ($this->sourceElementsToCompare as $colElement) {
                $this->matrixValues[$rowElement->id][$colElement->id] = ($rowElement->id == $colElement->id) ? 1 : null;
            }
        }
    }
    
    public function loadExistingComparison()
    {
        $comparison = AnpInterdependencyComparison::where('anp_analysis_id', $this->analysis->id)
            ->where('anp_dependency_id', $this->dependency->id)
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

    // public function updatedMatrixValues($value, $key)
    // {
    //     [$rowId, $colId] = explode('.', $key);
    //     if ($rowId == $colId) {
    //         $this->matrixValues[$rowId][$colId] = 1;
    //     } 
        
    // } tanpa resiprokal

    public function updatedMatrixValues($value, $key)
    {
        // Parse the key to get row and column IDs
        [$rowId, $colId] = explode('.', $key);
        
        // Skip if this is a diagonal element (always 1)
        if ($rowId == $colId) {
            $this->matrixValues[$rowId][$colId] = 1;
            return;
        }
        
        // Convert input value to float
        $floatValue = (float) $value;
        
        // Validation: ensure value is within valid range
        if ($floatValue < 0.11 || $floatValue > 9) {
            // Reset to previous value or default
            if (!isset($this->matrixValues[$rowId][$colId])) {
                $this->matrixValues[$rowId][$colId] = 1;
            }
            $this->addError("matrixValues.{$rowId}.{$colId}", 'Nilai harus antara 0.11 (1/9) dan 9');
            return;
        }
        
        // Clear any previous errors for this field
        $this->resetErrorBag("matrixValues.{$rowId}.{$colId}");
        $this->resetErrorBag("matrixValues.{$colId}.{$rowId}");
        
        // Update the current cell
        $this->matrixValues[$rowId][$colId] = $floatValue;
        
        // Calculate and update the reciprocal cell
        if ($floatValue > 0) {
            $reciprocalValue = round(1 / $floatValue, 4);
            $this->matrixValues[$colId][$rowId] = $reciprocalValue;
        }
        
        // Reset calculation results since matrix has changed
        $this->consistencyRatio = null;
        $this->isConsistent = null;
        $this->priorityVector = [];
        $this->calculationResult = null;
        
        // Optional: Auto-calculate consistency after each change (can be disabled for performance)
        // $this->recalculateConsistency();
    }

    public function recalculateConsistency()
    {
        if (count($this->sourceElementsToCompare) < 2) {
            if (count($this->sourceElementsToCompare) == 1) {
                $elId = $this->sourceElementsToCompare[0]->id;
                $this->priorityVector = [$elId => 1.0];
                $this->consistencyRatio = 0.0;
                $this->isConsistent = true;
                $this->calculationResult = ['priority_vector' => $this->priorityVector, 'consistency_ratio' => 0.0, 'is_consistent' => true];
                $this->dispatch('notify', ['message' => 'Hanya 1 elemen, prioritas otomatis 1.', 'type' => 'info']);
            }
            return;
        }

        $this->validate();

        $service = new AnpCalculationService();
        $matrixForCalc = [];
        foreach ($this->sourceElementsToCompare as $rowEl) {
            foreach ($this->sourceElementsToCompare as $colEl) {
                $matrixForCalc[$rowEl->id][$colEl->id] = (float) $this->matrixValues[$rowEl->id][$colEl->id];
            }
        }
        $this->calculationResult = $service->calculateEigenvectorAndCR($matrixForCalc);

        $this->priorityVector = $this->calculationResult['priority_vector'];
        $this->consistencyRatio = $this->calculationResult['consistency_ratio'];
        $this->isConsistent = $this->calculationResult['is_consistent'];
        $message = 'Kalkulasi selesai. CR: ' . round($this->consistencyRatio, 4) . ($this->isConsistent ? ' (Konsisten)' : ' (Tidak Konsisten!)');
        $this->dispatch('notify', ['message' => $message, 'type' => $this->isConsistent ? 'success' : 'error']);
    }

    public function saveComparisons()
    {
        $this->recalculateConsistency();

        if (is_null($this->isConsistent) || !$this->isConsistent) {
            $this->dispatch('notify', ['message' => 'Gagal menyimpan. Matriks tidak konsisten atau belum dihitung.', 'type' => 'error']);
            return;
        }
        
        $comparison = AnpInterdependencyComparison::updateOrCreate(
            ['anp_analysis_id' => $this->analysis->id, 'anp_dependency_id' => $this->dependency->id],
            [
                'comparison_data' => ['matrix_values' => $this->matrixValues],
                'priority_vector' => $this->priorityVector,
            ]
        );
        
        $comparison->consistency()->updateOrCreate([], [
            'consistency_ratio' => $this->consistencyRatio,
            'is_consistent' => $this->isConsistent,
        ]);

        $this->dispatch('notify', ['message' => 'Perbandingan interdependensi berhasil disimpan.', 'type' => 'success']);
    }

    private function findNextPendingInterdependencyComparison()
    {
        $allDependencyIds = $this->analysis->networkStructure->dependencies()->pluck('id');

        $completedDependencyIds = $this->analysis->interdependencyComparisons()->pluck('anp_dependency_id');

        $nextDependencyId = $allDependencyIds->diff($completedDependencyIds)->first();

        return $nextDependencyId ? AnpDependency::find($nextDependencyId) : null;
    }

    public function saveAndContinue()
    {
        $this->saveComparisons();
        if (!$this->isConsistent) {
            return;
        }

        $nextDependency = $this->findNextPendingInterdependencyComparison();

        if ($nextDependency) {
            return redirect()->route('hr.anp.analysis.interdependency.pairwise.form', [
                'anpAnalysis' => $this->analysis->id,
                'anpDependency' => $nextDependency->id
            ]);
        } else {
            $this->analysis->update(['status' => 'alternatives_pending']);

            $firstElementForAlternatives = $this->analysis->networkStructure->elements()->first();
            if ($firstElementForAlternatives) {
                return redirect()->route('hr.anp.analysis.alternative.pairwise.form', [
                    'anpAnalysis' => $this->analysis->id,
                    'anpElement' => $firstElementForAlternatives->id
                ]);
            }
        }
        
        $this->dispatch('notify', ['message' => 'Semua perbandingan interdependensi selesai, namun tidak ada kriteria untuk perbandingan alternatif.', 'type' => 'warning']);
    }

    public function render()
    {
        return view('livewire.hr.anp.pairwise-interdependencies-matrix');
    }
}