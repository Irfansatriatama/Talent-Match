<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use App\Models\AnpElement;
use App\Models\AnpAlternativeComparison;
use App\Models\User;
use App\Services\AnpCalculationService;
use App\Services\TestScoringService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class PairwiseAlternativesMatrix extends Component
{
    public $analysisId;
    public $analysis;
    public $alternatives;
    public $comparisons = [];
    public $criteria;
    public $currentCriterionIndex = 0;
    
    public $priorityVectors = [];
    public $consistencyRatios = [];
    public $isConsistent = [];
    public $calculationResults = [];
    
    protected $rules = [
        'comparisons.*.*.*.value' => 'required|numeric|min:0.11|max:9'
    ];
    
    protected $messages = [
        'comparisons.*.*.*.value.required' => 'Semua nilai perbandingan harus diisi.',
        'comparisons.*.*.*.value.numeric' => 'Nilai perbandingan harus angka.',
        'comparisons.*.*.*.value.min' => 'Nilai perbandingan minimal 1/9 (sekitar 0.11).',
        'comparisons.*.*.*.value.max' => 'Nilai perbandingan maksimal 9.',
    ];

    public function mount($analysisId)
    {
        $this->analysisId = $analysisId;
        $this->analysis = AnpAnalysis::with(['candidates', 'networkStructure.elements'])->findOrFail($analysisId);
        
        // Gunakan session key yang unik
        $sessionKey = 'pairwise_alternatives_comparisons_' . $this->analysisId;
        
        $this->alternatives = $this->analysis->candidates;
        $this->criteria = $this->analysis->networkStructure->elements;
        
        // Load dari session
        $savedComparisons = session($sessionKey, []);
        
        if (!empty($savedComparisons)) {
            $this->comparisons = $savedComparisons;
            $this->loadCalculationResults();
        } else {
            $this->initializeComparisons();
        }
        
        $this->loadExistingComparisons();
    }

    public function initializeComparisons()
    {
        $this->comparisons = [];
        $this->priorityVectors = [];
        $this->consistencyRatios = [];
        $this->isConsistent = [];
        $this->calculationResults = [];
        
        foreach ($this->criteria as $criterionIndex => $criterion) {
            $this->comparisons[$criterionIndex] = [];
            $this->priorityVectors[$criterionIndex] = [];
            $this->consistencyRatios[$criterionIndex] = null;
            $this->isConsistent[$criterionIndex] = null;
            $this->calculationResults[$criterionIndex] = null;
            
            foreach ($this->alternatives as $i => $altI) {
                foreach ($this->alternatives as $j => $altJ) {
                    if ($i == $j) {
                        $this->comparisons[$criterionIndex][$i][$j] = [
                            'value' => 1,
                            'alt_i_id' => $altI->id,
                            'alt_j_id' => $altJ->id
                        ];
                    } else {
                        $this->comparisons[$criterionIndex][$i][$j] = [
                            'value' => null,
                            'alt_i_id' => $altI->id,
                            'alt_j_id' => $altJ->id
                        ];
                    }
                }
            }
        }
    }

    public function loadExistingComparisons()
    {
        foreach ($this->criteria as $criterionIndex => $criterion) {
            $comparison = AnpAlternativeComparison::where('anp_analysis_id', $this->analysis->id)
                ->where('anp_element_id', $criterion->id)
                ->first();

            if ($comparison) {
                $matrixValues = $comparison->comparison_data['matrix_values'] ?? [];
                
                foreach ($this->alternatives as $i => $altI) {
                    foreach ($this->alternatives as $j => $altJ) {
                        if (isset($matrixValues[$altI->id][$altJ->id])) {
                            $this->comparisons[$criterionIndex][$i][$j]['value'] = $matrixValues[$altI->id][$altJ->id];
                        }
                    }
                }
                
                $this->priorityVectors[$criterionIndex] = $comparison->priority_vector ?? [];
                
                if ($comparison->consistency) {
                    $this->consistencyRatios[$criterionIndex] = $comparison->consistency->consistency_ratio;
                    $this->isConsistent[$criterionIndex] = $comparison->consistency->is_consistent;
                }
            }
        }
    }

    public function loadCalculationResults()
    {
        foreach ($this->criteria as $criterionIndex => $criterion) {
            if (!empty($this->comparisons[$criterionIndex])) {
                $this->calculateForCriterion($criterionIndex);
            }
        }
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'comparisons.')) {
            // Update reciprocal values
            preg_match('/comparisons\.(\d+)\.(\d+)\.(\d+)\.value/', $propertyName, $matches);
            if (count($matches) === 4) {
                $criterionIndex = (int)$matches[1];
                $i = (int)$matches[2];
                $j = (int)$matches[3];
                
                $value = (float)$this->comparisons[$criterionIndex][$i][$j]['value'];
                if ($value > 0 && $i !== $j) {
                    $this->comparisons[$criterionIndex][$j][$i]['value'] = round(1 / $value, 4);
                }
                
                // Save to session dengan key yang unik
                $sessionKey = 'pairwise_alternatives_comparisons_' . $this->analysisId;
                session([$sessionKey => $this->comparisons]);
                
                $this->calculateForCriterion($criterionIndex);
            }
        }
    }

    public function autoFillForCriterion($criterionIndex)
    {
        if (count($this->alternatives) < 2) return;

        $criterion = $this->criteria[$criterionIndex];
        $scoringService = new TestScoringService();
        $scores = [];

        foreach ($this->alternatives as $candidate) {
            if (strtolower($criterion->name) == 'keterampilan pemrograman' || strtolower($criterion->name) == 'programming') {
                $scores[$candidate->id] = $scoringService->getProgrammingScore($candidate);
            } elseif (str_contains(strtolower($criterion->name), 'riasec')) {
                $scores[$candidate->id] = $scoringService->getRiasecMatchScore($candidate, $this->analysis->jobPosition);
            } elseif (str_contains(strtolower($criterion->name), 'mbti')) {
                $scores[$candidate->id] = $scoringService->getMbtiMatchScore($candidate, $this->analysis->jobPosition);
            } else {
                $scores[$candidate->id] = 0.5;
            }
        }

        foreach ($this->alternatives as $i => $altI) {
            foreach ($this->alternatives as $j => $altJ) {
                if ($i == $j) {
                    $this->comparisons[$criterionIndex][$i][$j]['value'] = 1;
                } else {
                    if (isset($scores[$altI->id]) && isset($scores[$altJ->id]) && $scores[$altJ->id] != 0) {
                        $ratio = $scores[$altI->id] / $scores[$altJ->id];
                        $saatyValue = $this->convertToSaatyScale($ratio);
                        $this->comparisons[$criterionIndex][$i][$j]['value'] = $saatyValue;
                    } else {
                        $this->comparisons[$criterionIndex][$i][$j]['value'] = 1;
                    }
                }
            }
        }

        // Save to session
        $sessionKey = 'pairwise_alternatives_comparisons_' . $this->analysisId;
        session([$sessionKey => $this->comparisons]);

        $this->calculateForCriterion($criterionIndex);
        $this->dispatch('notify', ['message' => 'Matriks diisi otomatis berdasarkan skor tes. Harap periksa dan sesuaikan.', 'type' => 'info']);
    }

    private function convertToSaatyScale($ratio)
    {
        if ($ratio > 1) {
            if ($ratio >= 8.5) return 9;
            elseif ($ratio >= 7.5) return 8;
            elseif ($ratio >= 6.5) return 7;
            elseif ($ratio >= 5.5) return 6;
            elseif ($ratio >= 4.5) return 5;
            elseif ($ratio >= 3.5) return 4;
            elseif ($ratio >= 2.5) return 3;
            elseif ($ratio >= 1.5) return 2;
            else return 1;
        } elseif ($ratio < 1) {
            $invRatio = 1 / $ratio;
            if ($invRatio >= 8.5) return round(1/9, 3);
            elseif ($invRatio >= 7.5) return round(1/8, 3);
            elseif ($invRatio >= 6.5) return round(1/7, 3);
            elseif ($invRatio >= 5.5) return round(1/6, 3);
            elseif ($invRatio >= 4.5) return round(1/5, 3);
            elseif ($invRatio >= 3.5) return round(1/4, 3);
            elseif ($invRatio >= 2.5) return round(1/3, 3);
            elseif ($invRatio >= 1.5) return round(1/2, 3);
            else return 1;
        }
        return 1;
    }

    public function calculateForCriterion($criterionIndex)
    {
        try {
            $service = new AnpCalculationService();
            $matrixForCalc = [];
            
            foreach ($this->alternatives as $i => $altI) {
                foreach ($this->alternatives as $j => $altJ) {
                    if (isset($this->comparisons[$criterionIndex][$i][$j]['value']) && 
                        $this->comparisons[$criterionIndex][$i][$j]['value'] !== null) {
                        $matrixForCalc[$altI->id][$altJ->id] = (float) $this->comparisons[$criterionIndex][$i][$j]['value'];
                    }
                }
            }

            $this->calculationResults[$criterionIndex] = $service->calculateEigenvectorAndCR($matrixForCalc);

            if (isset($this->calculationResults[$criterionIndex]['error'])) {
                $this->dispatch('notify', ['message' => 'Error: ' . $this->calculationResults[$criterionIndex]['error'], 'type' => 'error']);
            } else {
                $this->priorityVectors[$criterionIndex] = $this->calculationResults[$criterionIndex]['priority_vector'];
                $this->consistencyRatios[$criterionIndex] = $this->calculationResults[$criterionIndex]['consistency_ratio'];
                $this->isConsistent[$criterionIndex] = $this->calculationResults[$criterionIndex]['is_consistent'];
                
                $criterion = $this->criteria[$criterionIndex];
                $message = 'Kalkulasi untuk ' . $criterion->name . ' selesai. CR: ' . round($this->consistencyRatios[$criterionIndex], 4) . 
                          ($this->isConsistent[$criterionIndex] ? ' (Konsisten)' : ' (Tidak Konsisten!)');
                $this->dispatch('notify', ['message' => $message, 'type' => $this->isConsistent[$criterionIndex] ? 'success' : 'error']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error calculating consistency: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function calculateAllConsistency()
    {
        foreach ($this->criteria as $criterionIndex => $criterion) {
            $this->calculateForCriterion($criterionIndex);
        }
    }

    public function validateMatrix($criterionIndex): bool
    {
        $hasEmptyCells = false;
        $emptyCount = 0;
        
        foreach ($this->alternatives as $i => $altI) {
            foreach ($this->alternatives as $j => $altJ) {
                if ($i !== $j) {
                    $value = $this->comparisons[$criterionIndex][$i][$j]['value'] ?? null;
                    if ($value === null || $value === '' || !is_numeric($value)) {
                        $hasEmptyCells = true;
                        $emptyCount++;
                    }
                }
            }
        }
        
        if ($hasEmptyCells) {
            $criterion = $this->criteria[$criterionIndex];
            $this->dispatch('notify', [
                'message' => "Perhatian pada kriteria {$criterion->name}: Ada {$emptyCount} sel yang belum diisi. Harap lengkapi semua nilai perbandingan.",
                'type' => 'warning'
            ]);
            return false;
        }
        
        return true;
    }

    public function saveComparisons()
    {
        $allValid = true;
        
        foreach ($this->criteria as $criterionIndex => $criterion) {
            if (!$this->validateMatrix($criterionIndex)) {
                $allValid = false;
                continue;
            }
            
            if (is_null($this->isConsistent[$criterionIndex])) {
                $this->calculateForCriterion($criterionIndex);
            }
            
            if (!$this->isConsistent[$criterionIndex]) {
                $this->dispatch('notify', ['message' => "Matriks untuk kriteria {$criterion->name} tidak konsisten.", 'type' => 'error']);
                $allValid = false;
                continue;
            }
            
            $comparisonDataForDb = [];
            foreach ($this->alternatives as $i => $altI) {
                foreach ($this->alternatives as $j => $altJ) {
                    $comparisonDataForDb[$altI->id][$altJ->id] = (float) $this->comparisons[$criterionIndex][$i][$j]['value'];
                }
            }

            $comparison = AnpAlternativeComparison::updateOrCreate(
                [
                    'anp_analysis_id' => $this->analysis->id,
                    'anp_element_id' => $criterion->id,
                ],
                [
                    'comparison_data' => [
                        'matrix_values' => $comparisonDataForDb, 
                        'alternative_ids' => $this->alternatives->pluck('id')->all()
                    ],
                    'priority_vector' => $this->priorityVectors[$criterionIndex],
                ]
            );
            
            $comparison->consistency()->updateOrCreate(
                [],
                [
                    'consistency_ratio' => $this->consistencyRatios[$criterionIndex],
                    'is_consistent' => $this->isConsistent[$criterionIndex],
                ]
            );
        }
        
        if ($allValid) {
            $this->dispatch('notify', ['message' => 'Semua perbandingan alternatif berhasil disimpan.', 'type' => 'success']);
        }
        
        return $allValid;
    }

    public function saveAndContinue()
    {
        if (!$this->saveComparisons()) {
            return;
        }
        
        // Hapus session setelah berhasil disimpan
        $sessionKey = 'pairwise_alternatives_comparisons_' . $this->analysisId;
        session()->forget($sessionKey);
        
        $this->calculateAndFinish();
    }

    public function calculateAndFinish()
    {
        Log::info('Mengecek kesiapan total analisis...');
        $this->analysis->refresh();
        
        if (!$this->analysis->isReadyForCalculation()) {
            $missingComparisons = $this->findMissingComparisons();
            Log::error('PROSES GAGAL: isReadyForCalculation() return false. Bagian yang kurang: ' . $missingComparisons);
            
            $this->dispatch('notify', [
                'message' => 'Masih ada perbandingan yang belum selesai: ' . $missingComparisons,
                'type' => 'warning'
            ]);
            return;
        }

        $this->dispatch('notify', [
            'message' => 'Semua perbandingan valid. Memproses peringkat akhir, mohon tunggu...',
            'type' => 'info'
        ]);

        Log::info('Analisis siap! Memulai proses kalkulasi di AnpCalculationService.');
        
        try {
            $this->analysis->update(['status' => 'calculating']);
            
            $calculationService = app(AnpCalculationService::class);
            $result = $calculationService->processAnalysis($this->analysis);

            Log::info("ANP Calculation completed for Analysis ID: {$this->analysis->id}", $result);

            return redirect()->route('h-r.anp.analysis.show', $this->analysis->id)
                ->with('success', 'Kalkulasi ANP berhasil diselesaikan!');

        } catch (\Exception $e) {
            $this->analysis->update(['status' => 'alternatives_pending']);
            
            Log::error("ANP Calculation failed for Analysis ID: {$this->analysis->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', [
                'message' => 'Terjadi error saat menghitung peringkat akhir: ' . $e->getMessage(),
                'type' => 'error'
            ]);
            return;
        }
    }

    private function findMissingComparisons(): string
    {
        $analysis = $this->analysis->load([
            'networkStructure.elements',
            'networkStructure.clusters', 
            'networkStructure.dependencies',
            'criteriaComparisons.consistency',
            'interdependencyComparisons.consistency',
            'alternativeComparisons.consistency'
        ]);
        
        $missing = [];
        
        $criteriaValid = $analysis->criteriaComparisons()
            ->whereHas('consistency', fn($q) => $q->where('is_consistent', true))
            ->exists();
        
        if (!$criteriaValid) {
            $missing[] = "Perbandingan kriteria/cluster vs goal";
        }
        
        $dependencyCount = $analysis->networkStructure->dependencies()->count();
        $validInterdepCount = $analysis->interdependencyComparisons()
            ->whereHas('consistency', fn($q) => $q->where('is_consistent', true))
            ->count();
        
        if ($dependencyCount > 0 && $validInterdepCount < $dependencyCount) {
            $missing[] = "Perbandingan interdependensi ({$validInterdepCount}/{$dependencyCount} selesai)";
        }
        
        $elementCount = $analysis->networkStructure->elements()->count();
        $validAltCount = $analysis->alternativeComparisons()
            ->whereHas('consistency', fn($q) => $q->where('is_consistent', true))
            ->count();
        
        if ($validAltCount < $elementCount) {
            $missing[] = "Perbandingan alternatif ({$validAltCount}/{$elementCount} selesai)";
        }
        
        return implode(', ', $missing);
    }

    public function getCurrentCriterion()
    {
        return $this->criteria[$this->currentCriterionIndex] ?? null;
    }

    public function nextCriterion()
    {
        if ($this->currentCriterionIndex < count($this->criteria) - 1) {
            $this->currentCriterionIndex++;
        }
    }

    public function previousCriterion()
    {
        if ($this->currentCriterionIndex > 0) {
            $this->currentCriterionIndex--;
        }
    }

    public function setCriterion($index)
    {
        if ($index >= 0 && $index < count($this->criteria)) {
            $this->currentCriterionIndex = $index;
        }
    }

    public function render()
    {
        return view('livewire.h-r.anp.pairwise-alternatives-matrix');
    }
}