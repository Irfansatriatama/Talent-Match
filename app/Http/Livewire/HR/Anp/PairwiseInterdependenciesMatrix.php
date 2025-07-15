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
    public $analysisId;
    public $analysis;
    public $dependencies;
    public $comparisons = [];
    public $currentDependencyIndex = 0;
    public $priorityVectors = [];
    public $consistencyRatios = [];
    public $consistencyStatus = [];
    
    // Current dependency data
    public $currentDependency;
    public $sourceElementsToCompare = [];
    public $targetNodeDescription;
    
    protected $rules = [
        'comparisons.*.*.*.value' => 'required|numeric|min:0.1|max:9'
    ];
    
    protected $messages = [
        'comparisons.*.*.*.value.required' => 'Semua nilai perbandingan harus diisi.',
        'comparisons.*.*.*.value.numeric' => 'Nilai perbandingan harus angka.',
        'comparisons.*.*.*.value.min' => 'Nilai perbandingan minimal 0.1 (1/10).',
        'comparisons.*.*.*.value.max' => 'Nilai perbandingan maksimal 9.',
    ];

    public function mount($analysisId)
    {
        $this->analysisId = $analysisId;
        $this->analysis = AnpAnalysis::with(['networkStructure.dependencies'])->findOrFail($analysisId);
        
        // Gunakan session key yang unik
        $sessionKey = 'pairwise_interdependencies_comparisons_' . $this->analysisId;
        
        $this->dependencies = $this->analysis->networkStructure->dependencies()
            ->with(['sourceable', 'targetable'])
            ->get()
            ->groupBy(function($dependency) {
                return $dependency->targetable_type . '_' . $dependency->targetable_id;
            });
        
        // Load dari session dengan key yang unik
        $savedComparisons = session($sessionKey, []);
        
        if (!empty($savedComparisons)) {
            $this->comparisons = $savedComparisons;
            $this->loadCalculationResults();
        } else {
            $this->initializeComparisons();
        }
        
        $this->loadExistingComparisons();
        $this->setCurrentDependency();
    }

    public function initializeComparisons()
    {
        $this->comparisons = [];
        $this->priorityVectors = [];
        $this->consistencyRatios = [];
        $this->consistencyStatus = [];

        foreach ($this->dependencies as $groupKey => $dependencyGroup) {
            $this->comparisons[$groupKey] = [];
            $this->priorityVectors[$groupKey] = [];
            $this->consistencyRatios[$groupKey] = null;
            $this->consistencyStatus[$groupKey] = null;

            // Get source elements to compare
            $sourceElements = $this->getSourceElementsForDependencyGroup($dependencyGroup);
            
            // Initialize comparison matrix
            foreach ($sourceElements as $rowElement) {
                foreach ($sourceElements as $colElement) {
                    if ($rowElement->id == $colElement->id) {
                        $this->comparisons[$groupKey][$rowElement->id][$colElement->id] = ['value' => 1];
                    } else {
                        $this->comparisons[$groupKey][$rowElement->id][$colElement->id] = ['value' => null];
                    }
                }
            }
        }
    }

    public function getSourceElementsForDependencyGroup($dependencyGroup)
    {
        $firstDependency = $dependencyGroup->first();
        
        if ($firstDependency->sourceable_type == AnpCluster::class) {
            $sourceCluster = AnpCluster::with('elements')->find($firstDependency->sourceable_id);
            return $sourceCluster ? $sourceCluster->elements : collect();
        } else if ($firstDependency->sourceable_type == AnpElement::class) {
            $siblingDependencies = AnpDependency::where('anp_network_structure_id', $this->analysis->anp_network_structure_id)
                ->where('targetable_id', $firstDependency->targetable_id)
                ->where('targetable_type', $firstDependency->targetable_type)
                ->where('sourceable_type', $firstDependency->sourceable_type)
                ->with('sourceable')
                ->get();

            return $siblingDependencies->pluck('sourceable')->unique('id');
        }
        
        return collect();
    }

    public function setCurrentDependency()
    {
        $dependencyGroups = $this->dependencies->keys()->toArray();
        
        if (isset($dependencyGroups[$this->currentDependencyIndex])) {
            $currentGroupKey = $dependencyGroups[$this->currentDependencyIndex];
            $this->currentDependency = $this->dependencies[$currentGroupKey]->first();
            $this->sourceElementsToCompare = $this->getSourceElementsForDependencyGroup($this->dependencies[$currentGroupKey]);
            $this->targetNodeDescription = $this->currentDependency->targetable->name . ' (' . 
                ($this->currentDependency->targetable_type == AnpElement::class ? 'Elemen' : 'Cluster') . ')';
        }
    }

    public function loadExistingComparisons()
    {
        foreach ($this->dependencies as $groupKey => $dependencyGroup) {
            $firstDependency = $dependencyGroup->first();
            
            $comparison = AnpInterdependencyComparison::where('anp_analysis_id', $this->analysis->id)
                ->where('anp_dependency_id', $firstDependency->id)
                ->first();

            if ($comparison) {
                if (isset($comparison->comparison_data['matrix_values'])) {
                    $matrixValues = $comparison->comparison_data['matrix_values'];
                    foreach ($matrixValues as $rowId => $row) {
                        foreach ($row as $colId => $value) {
                            $this->comparisons[$groupKey][$rowId][$colId] = ['value' => $value];
                        }
                    }
                }
                
                $this->priorityVectors[$groupKey] = $comparison->priority_vector ?? [];
                
                if ($comparison->consistency) {
                    $this->consistencyRatios[$groupKey] = $comparison->consistency->consistency_ratio;
                    $this->consistencyStatus[$groupKey] = $comparison->consistency->is_consistent;
                }
            }
        }
    }

    public function loadCalculationResults()
    {
        // Load calculation results from saved comparisons
        foreach ($this->comparisons as $groupKey => $comparisonData) {
            if ($this->hasCompleteMatrix($groupKey)) {
                $this->calculateConsistencyForGroup($groupKey);
            }
        }
    }

    public function hasCompleteMatrix($groupKey)
    {
        if (!isset($this->comparisons[$groupKey])) {
            return false;
        }

        foreach ($this->comparisons[$groupKey] as $rowId => $row) {
            foreach ($row as $colId => $cell) {
                if ($rowId != $colId && (is_null($cell['value']) || $cell['value'] === '')) {
                    return false;
                }
            }
        }
        return true;
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'comparisons.')) {
            // Parse the property name to get group key and matrix positions
            $parts = explode('.', $propertyName);
            if (count($parts) >= 4) {
                $groupKey = $parts[1];
                $rowId = $parts[2];
                $colId = $parts[3];
                
                if ($rowId != $colId) {
                    $value = $this->comparisons[$groupKey][$rowId][$colId]['value'];
                    
                    if ($value !== null && $value !== '' && is_numeric($value)) {
                        $floatValue = (float) $value;
                        
                        // Validate range
                        if ($floatValue >= 0.1 && $floatValue <= 9) {
                            // Update reciprocal value
                            $reciprocalValue = round(1 / $floatValue, 4);
                            $this->comparisons[$groupKey][$colId][$rowId]['value'] = $reciprocalValue;
                            
                            // Reset consistency calculations for this group
                            $this->consistencyRatios[$groupKey] = null;
                            $this->consistencyStatus[$groupKey] = null;
                            $this->priorityVectors[$groupKey] = [];
                        }
                    }
                }
            }
            
            // Save to session dengan key yang unik
            $sessionKey = 'pairwise_interdependencies_comparisons_' . $this->analysisId;
            session([$sessionKey => $this->comparisons]);
        }
    }

    public function calculateConsistencyForGroup($groupKey)
    {
        if (!$this->hasCompleteMatrix($groupKey)) {
            $this->dispatch('notify', ['message' => 'Harap lengkapi semua nilai perbandingan terlebih dahulu.', 'type' => 'error']);
            return;
        }

        $service = new AnpCalculationService();
        $matrixForCalc = [];
        
        foreach ($this->comparisons[$groupKey] as $rowId => $row) {
            foreach ($row as $colId => $cell) {
                $matrixForCalc[$rowId][$colId] = (float) $cell['value'];
            }
        }

        $calculationResult = $service->calculateEigenvectorAndCR($matrixForCalc);

        if (isset($calculationResult['error'])) {
            $this->dispatch('notify', ['message' => 'Error: ' . $calculationResult['error'], 'type' => 'error']);
        } else {
            $this->priorityVectors[$groupKey] = $calculationResult['priority_vector'];
            $this->consistencyRatios[$groupKey] = $calculationResult['consistency_ratio'];
            $this->consistencyStatus[$groupKey] = $calculationResult['is_consistent'];
            
            $message = 'Kalkulasi selesai. CR: ' . round($this->consistencyRatios[$groupKey], 4) . 
                ($this->consistencyStatus[$groupKey] ? ' (Konsisten)' : ' (Tidak Konsisten!)');
            $this->dispatch('notify', ['message' => $message, 'type' => $this->consistencyStatus[$groupKey] ? 'success' : 'error']);
        }
    }

    public function recalculateConsistency()
    {
        $dependencyGroups = $this->dependencies->keys()->toArray();
        $currentGroupKey = $dependencyGroups[$this->currentDependencyIndex];
        
        $this->calculateConsistencyForGroup($currentGroupKey);
    }

    public function nextDependency()
    {
        $dependencyGroups = $this->dependencies->keys()->toArray();
        $currentGroupKey = $dependencyGroups[$this->currentDependencyIndex];
        
        if (!$this->consistencyStatus[$currentGroupKey]) {
            $this->dispatch('notify', ['message' => 'Harap pastikan matriks konsisten sebelum melanjutkan.', 'type' => 'error']);
            return;
        }

        if ($this->currentDependencyIndex < count($dependencyGroups) - 1) {
            $this->currentDependencyIndex++;
            $this->setCurrentDependency();
        }
    }

    public function previousDependency()
    {
        if ($this->currentDependencyIndex > 0) {
            $this->currentDependencyIndex--;
            $this->setCurrentDependency();
        }
    }

    public function saveComparisons()
    {
        $allConsistent = true;
        
        foreach ($this->dependencies as $groupKey => $dependencyGroup) {
            if (!$this->consistencyStatus[$groupKey]) {
                $allConsistent = false;
                break;
            }
        }

        if (!$allConsistent) {
            $this->dispatch('notify', ['message' => 'Semua matriks harus konsisten sebelum menyimpan.', 'type' => 'error']);
            return;
        }

        foreach ($this->dependencies as $groupKey => $dependencyGroup) {
            $firstDependency = $dependencyGroup->first();
            
            // Convert comparisons to matrix values format
            $matrixValues = [];
            foreach ($this->comparisons[$groupKey] as $rowId => $row) {
                foreach ($row as $colId => $cell) {
                    $matrixValues[$rowId][$colId] = $cell['value'];
                }
            }
            
            $comparison = AnpInterdependencyComparison::updateOrCreate(
                [
                    'anp_analysis_id' => $this->analysis->id, 
                    'anp_dependency_id' => $firstDependency->id
                ],
                [
                    'comparison_data' => ['matrix_values' => $matrixValues],
                    'priority_vector' => $this->priorityVectors[$groupKey],
                ]
            );
            
            $comparison->consistency()->updateOrCreate([], [
                'consistency_ratio' => $this->consistencyRatios[$groupKey],
                'is_consistent' => $this->consistencyStatus[$groupKey],
            ]);
        }

        $this->dispatch('notify', ['message' => 'Semua perbandingan interdependensi berhasil disimpan.', 'type' => 'success']);
    }

    public function findNextPendingInterdependencyComparison()
    {
        $allDependencyIds = $this->analysis->networkStructure->dependencies()->pluck('id');
        $completedDependencyIds = $this->analysis->interdependencyComparisons()->pluck('anp_dependency_id');
        $nextDependencyId = $allDependencyIds->diff($completedDependencyIds)->first();

        return $nextDependencyId ? AnpDependency::find($nextDependencyId) : null;
    }

    public function saveAndContinue()
    {
        $this->saveComparisons();
        
        // Check if all matrices are consistent
        $allConsistent = true;
        foreach ($this->consistencyStatus as $status) {
            if (!$status) {
                $allConsistent = false;
                break;
            }
        }

        if (!$allConsistent) {
            return;
        }

        // Hapus session setelah berhasil disimpan
        $sessionKey = 'pairwise_interdependencies_comparisons_' . $this->analysisId;
        session()->forget($sessionKey);

        $nextDependency = $this->findNextPendingInterdependencyComparison();

        if ($nextDependency) {
            return redirect()->route('h-r.anp.analysis.interdependency.pairwise.form', [
                'anpAnalysis' => $this->analysis->id,
                'anpDependency' => $nextDependency->id
            ]);
        } else {
            $this->analysis->update(['status' => 'alternatives_pending']);

            $firstElementForAlternatives = $this->analysis->networkStructure->elements()->first();
            if ($firstElementForAlternatives) {
                return redirect()->route('h-r.anp.analysis.alternative.pairwise.form', [
                    'anpAnalysis' => $this->analysis->id,
                    'anpElement' => $firstElementForAlternatives->id
                ]);
            }
        }
        
        $this->dispatch('notify', ['message' => 'Semua perbandingan interdependensi selesai, namun tidak ada kriteria untuk perbandingan alternatif.', 'type' => 'warning']);
    }

    public function getCurrentProgress()
    {
        $total = count($this->dependencies);
        $completed = 0;
        
        foreach ($this->consistencyStatus as $status) {
            if ($status) {
                $completed++;
            }
        }
        
        return [
            'current' => $this->currentDependencyIndex + 1,
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
        ];
    }

    public function render()
    {
        return view('livewire.h-r.anp.pairwise-interdependencies-matrix');
    }
}