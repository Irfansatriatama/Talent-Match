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
    public AnpAnalysis $analysis;
    public AnpElement $criterionElement;

    public $alternativesToCompare = []; 
    
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

    public function mount(AnpAnalysis $anpAnalysis, AnpElement $anpElement)
    {
        $this->analysis = $anpAnalysis->load('candidates'); 
        $this->criterionElement = $anpElement;
        $this->alternativesToCompare = $this->analysis->candidates->all();

        $this->initializeMatrix();
        $this->loadExistingComparison();
    }

    public function initializeMatrix()
    {
        $this->matrixValues = [];
        foreach ($this->alternativesToCompare as $rowCand) {
            foreach ($this->alternativesToCompare as $colCand) {
                if ($rowCand->id == $colCand->id) {
                    $this->matrixValues[$rowCand->id][$colCand->id] = 1;
                } else {
                    $this->matrixValues[$rowCand->id][$colCand->id] = null;
                }
            }
        }
    }
    
    public function loadExistingComparison()
    {
        $comparison = AnpAlternativeComparison::where('anp_analysis_id', $this->analysis->id)
            ->where('anp_element_id', $this->criterionElement->id)
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

    public function autoFillMatrix()
    {
        if (count($this->alternativesToCompare) < 2) return;

        $scoringService = new TestScoringService();
        $scores = [];
        foreach ($this->alternativesToCompare as $candidate) {
            if (strtolower($this->criterionElement->name) == 'keterampilan pemrograman' || strtolower($this->criterionElement->name) == 'programming') {
                $scores[$candidate->id] = $scoringService->getProgrammingScore($candidate);
            } elseif (str_contains(strtolower($this->criterionElement->name), 'riasec')) {
                $scores[$candidate->id] = $scoringService->getRiasecMatchScore($candidate, $this->analysis->jobPosition);
            } elseif (str_contains(strtolower($this->criterionElement->name), 'mbti')) {
                 $scores[$candidate->id] = $scoringService->getMbtiMatchScore($candidate, $this->analysis->jobPosition);
            }
            else {
                $scores[$candidate->id] = 0.5;
            }
        }

        foreach ($this->alternativesToCompare as $rowCand) {
            foreach ($this->alternativesToCompare as $colCand) {
                if ($rowCand->id == $colCand->id) {
                    $this->matrixValues[$rowCand->id][$colCand->id] = 1;
                } else {
                    if (isset($scores[$rowCand->id]) && isset($scores[$colCand->id]) && $scores[$colCand->id] != 0) {
                        $ratio = $scores[$rowCand->id] / $scores[$colCand->id];
                        $saatyValue = 1;
                        if ($ratio > 1) {
                            if ($ratio >= 8.5) $saatyValue = 9;
                            elseif ($ratio >= 7.5) $saatyValue = 8;
                            elseif ($ratio >= 6.5) $saatyValue = 7;
                            elseif ($ratio >= 5.5) $saatyValue = 6;
                            elseif ($ratio >= 4.5) $saatyValue = 5;
                            elseif ($ratio >= 3.5) $saatyValue = 4;
                            elseif ($ratio >= 2.5) $saatyValue = 3;
                            elseif ($ratio >= 1.5) $saatyValue = 2;
                            else $saatyValue = 1; 
                        } elseif ($ratio < 1) { 
                             $invRatio = 1 / $ratio;
                            if ($invRatio >= 8.5) $saatyValue = round(1/9, 3);
                            elseif ($invRatio >= 7.5) $saatyValue = round(1/8, 3);
                            elseif ($invRatio >= 6.5) $saatyValue = round(1/7, 3);
                            elseif ($invRatio >= 5.5) $saatyValue = round(1/6, 3);
                            elseif ($invRatio >= 4.5) $saatyValue = round(1/5, 3);
                            elseif ($invRatio >= 3.5) $saatyValue = round(1/4, 3);
                            elseif ($invRatio >= 2.5) $saatyValue = round(1/3, 3);
                            elseif ($invRatio >= 1.5) $saatyValue = round(1/2, 3);
                            else $saatyValue = 1;
                        }
                        $this->matrixValues[$rowCand->id][$colCand->id] = $saatyValue;
                    } else {
                        $this->matrixValues[$rowCand->id][$colCand->id] = 1;
                    }
                }
            }
        }
        $this->dispatch('notify', ['message' => 'Matriks diisi otomatis berdasarkan skor tes. Harap periksa dan sesuaikan.', 'type' => 'info']);
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
        $this->recalculateConsistency();

        if (is_null($this->isConsistent)) {
            $this->dispatch('notify', ['message' => 'Gagal menyimpan. Hitung konsistensi dulu.', 'type' => 'error']);
            return;
        }
         if (!$this->isConsistent) {
            $this->dispatch('notify', ['message' => 'Gagal menyimpan. Matriks tidak konsisten.', 'type' => 'error']);
            return;
        }
        
        $comparisonDataForDb = [];
        foreach($this->alternativesToCompare as $rowCand){
            foreach($this->alternativesToCompare as $colCand){
                $comparisonDataForDb[$rowCand->id][$colCand->id] = (float) $this->matrixValues[$rowCand->id][$colCand->id];
            }
        }

        $comparison = AnpAlternativeComparison::updateOrCreate(
            [
                'anp_analysis_id' => $this->analysis->id,
                'anp_element_id' => $this->criterionElement->id,
            ],
            [
                'comparison_data' => ['matrix_values' => $comparisonDataForDb, 'alternative_ids' => collect($this->alternativesToCompare)->pluck('id')->all()],
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

        $this->dispatch('notify', ['message' => 'Perbandingan alternatif berhasil disimpan.', 'type' => 'success']);
    }

    public function calculateAndFinish()
    {
        $this->saveComparisons();

        if (!$this->isConsistent) {
            Log::warning('Proses berhenti: Matriks saat ini tidak konsisten.'); 
            $this->dispatch('notify', [
                'message' => 'Perbandingan untuk kriteria ini belum konsisten. Harap hitung konsistensi terlebih dahulu.',
                'type' => 'error'
            ]);
            return;
        }

        $nextElement = $this->findNextPendingAlternativeComparison();
        if ($nextElement) {
            Log::info('Proses dialihkan ke perbandingan alternatif berikutnya.'); 
            return redirect()->route('h-r.anp.analysis.alternative.pairwise.form', [
                'anpAnalysis' => $this->analysis->id,
                'anpElement' => $nextElement->id
            ]);
        }

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
            
            $calculationService = app(\App\Services\AnpCalculationService::class);
            
            $result = $calculationService->processAnalysis($this->analysis);

            \Log::info("ANP Calculation completed for Analysis ID: {$this->analysis->id}", $result);

            return redirect()->route('h-r.anp.analysis.show', $this->analysis->id)
                ->with('success', 'Kalkulasi ANP berhasil diselesaikan!');

        } catch (\Exception $e) {
            $this->analysis->update(['status' => 'alternatives_pending']);
            
            \Log::error("ANP Calculation failed for Analysis ID: {$this->analysis->id}", [
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

    private function findNextPendingAlternativeComparison()
    {
        $analysis = $this->analysis->load(['networkStructure.elements']);
        $allCriteriaElements = $analysis->networkStructure->elements;
        $completedAlternativeComps = $analysis->alternativeComparisons()->pluck('anp_element_id');

        foreach ($allCriteriaElements as $element) {
            if (!$completedAlternativeComps->contains($element->id) && $element->id != $this->criterionElement->id) {
                return $element;
            }
        }
        
        return null;
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


    public function render()
    {
        return view('livewire.h-r.anp.pairwise-alternatives-matrix');
    }
}