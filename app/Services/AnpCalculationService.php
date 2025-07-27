<?php

namespace App\Services;

use App\Models\AnpAnalysis;
use App\Models\AnpCluster;
use App\Models\AnpDependency;
use App\Models\AnpElement;
use App\Models\User;
use App\Models\AnpStructureSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AnpCalculationService
{
    protected array $randomIndices;
    protected float $consistencyThreshold;
    protected int $maxIterations;
    protected float $convergenceTolerance;

    public function __construct()
    {
        $this->randomIndices = config('anp.random_indices');
        $this->consistencyThreshold = config('anp.consistency_ratio_threshold', 0.10);
        $this->maxIterations = config('anp.limit_supermatrix.max_iterations', 100);
        $this->convergenceTolerance = config('anp.limit_supermatrix.tolerance', 0.00001);
    }

    public function processAnalysis(AnpAnalysis $analysis): array
    {
        Log::info("Memulai kalkulasi ANP untuk Analisis ID: {$analysis->id}");
        
        return DB::transaction(function () use ($analysis) {
            try {
                $analysis->update(['status' => 'calculating']);
                
                $priorityData = $this->gatherAllValidPriorityVectors($analysis);
                Log::info("[ANP ID:{$analysis->id}] Berhasil mengumpulkan data prioritas.");
                
                $this->debugLogClusterWeights($analysis, $priorityData);

                $unweightedSupermatrix = $this->buildUnweightedSupermatrix($analysis, $priorityData);
                Log::info("[ANP ID:{$analysis->id}] Berhasil membangun Unweighted Supermatrix.");
                
                $weightedSupermatrix = $this->buildWeightedSupermatrix($unweightedSupermatrix, $analysis, $priorityData);
                Log::info("[ANP ID:{$analysis->id}] Berhasil membangun Weighted Supermatrix.");

                $limitSupermatrix = $this->calculateLimitSupermatrix($weightedSupermatrix);
                Log::info("[ANP ID:{$analysis->id}] Berhasil menghitung Limit Supermatrix.");

                $finalScores = $this->extractFinalScores($limitSupermatrix, $priorityData);
                Log::info("[ANP ID:{$analysis->id}] Berhasil menyintesis hasil akhir.");

                $this->saveResults($analysis, $finalScores);
                Log::info("[ANP ID:{$analysis->id}] Berhasil menyimpan hasil ranking.");

                $analysis->update([
                    'status' => 'completed',
                    'calculation_data' => [
                        'unweighted_supermatrix_preview' => array_slice($unweightedSupermatrix, 0, 5),
                        'weighted_supermatrix_preview' => array_slice($weightedSupermatrix, 0, 5),
                        'limit_supermatrix_preview' => array_slice($limitSupermatrix, 0, 5),
                        'calculation_timestamp' => now()->toIso8601String()
                    ],
                    'completed_at' => now()
                ]);

                Log::info("Kalkulasi ANP berhasil untuk Analisis ID: {$analysis->id}");
                return ['status' => 'success', 'results' => $finalScores];

            } catch (Exception $e) {
                Log::error("Kalkulasi ANP GAGAL untuk Analisis ID: {$analysis->id}", [
                    'error_message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                $analysis->update(['status' => 'error']);
                
                throw $e; 
            }
        });
    }

    private function debugLogClusterWeights(AnpAnalysis $analysis, array $priorityData): void
    {
        Log::info("[ANP ID:{$analysis->id}] Debug - Total criteria weights: " . count($priorityData['criteriaWeights']));
        
        foreach ($priorityData['criteriaWeights'] as $comp) {
            Log::info("[ANP ID:{$analysis->id}] Debug - Criteria comparison:", [
                'control_type' => $comp->control_criterionable_type,
                'control_id' => $comp->control_criterionable_id,
                'compared_type' => $comp->compared_elements_type,
                'priority_vector' => $comp->priority_vector,
                'has_consistency' => $comp->consistency ? 'yes' : 'no',
                'is_consistent' => $comp->consistency ? $comp->consistency->is_consistent : 'N/A'
            ]);
        }
    }

    public function calculateEigenvectorAndCR(array $matrix): array
    {
        $n = count($matrix);
        
        if ($n === 0) return ['priority_vector' => [], 'is_consistent' => true, 'consistency_ratio' => 0.0, 'lambda_max' => 0, 'consistency_index' => 0, 'random_index' => 0];
        if ($n === 1) {
            $key = array_key_first($matrix);
            return ['priority_vector' => [$key => 1.0], 'is_consistent' => true, 'consistency_ratio' => 0.0, 'lambda_max' => 1, 'consistency_index' => 0, 'random_index' => 0];
        }

        $keys = array_keys($matrix);
        $numericMatrix = array_values(array_map('array_values', $matrix));
        
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if (!is_numeric($numericMatrix[$i][$j]) || $numericMatrix[$i][$j] <= 0) {
                    Log::warning("Invalid matrix value at [{$i}][{$j}]: " . $numericMatrix[$i][$j]);
                    $numericMatrix[$i][$j] = 1.0;
                }
                $numericMatrix[$i][$j] = (float) $numericMatrix[$i][$j];
            }
        }
        
        $columnSums = array_fill(0, $n, 0.0);
        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $columnSums[$j] += $numericMatrix[$i][$j];
            }
        }

        $priorityVectorNumeric = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            $rowSum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $rowSum += ($columnSums[$j] > 0) ? $numericMatrix[$i][$j] / $columnSums[$j] : 0;
            }
            $priorityVectorNumeric[$i] = $rowSum / $n;
        }
        
        $weightedVector = [];
        for ($i = 0; $i < $n; $i++) {
            $sum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $sum += $numericMatrix[$i][$j] * $priorityVectorNumeric[$j];
            }
            $weightedVector[$i] = $sum;
        }

        $lambdaMax = 0.0;
        for ($i = 0; $i < $n; $i++) {
            if ($priorityVectorNumeric[$i] > 0) {
                $lambdaMax += $weightedVector[$i] / $priorityVectorNumeric[$i];
            }
        }
        $lambdaMax /= $n;

        $ci = ($n > 2) ? ($lambdaMax - $n) / ($n - 1) : 0;
        
        $ri = $this->randomIndices[$n] ?? $this->randomIndices[10] ?? 1.49;
        
        $cr = ($ri > 0) ? $ci / $ri : 0;

        Log::info("ANP Calculation Debug:", [
            'matrix_size' => $n,
            'lambda_max' => round($lambdaMax, 6),
            'consistency_index' => round($ci, 6),
            'random_index' => $ri,
            'consistency_ratio' => round($cr, 6),
            'is_consistent' => round($cr, 6) <= $this->consistencyThreshold
        ]);

        return [
            'priority_vector' => array_combine($keys, $priorityVectorNumeric),
            'lambda_max' => round($lambdaMax, 6),
            'consistency_index' => round($ci, 6),
            'random_index' => $ri,
            'consistency_ratio' => round($cr, 6),
            'is_consistent' => round($cr, 6) <= $this->consistencyThreshold,
        ];
    }

    private function gatherAllValidPriorityVectors(AnpAnalysis $analysis): array
    {
        $elementsMap = [];
        $nodeList = [];
        $idx = 0;
        
        $analysis->load([
            'networkStructure.elements.cluster', 
            'networkStructure.clusters.elements', 
            'networkStructure.dependencies.sourceable', 
            'networkStructure.dependencies.targetable', 
            'candidates',
            'criteriaComparisons.consistency',
            'interdependencyComparisons.consistency',
            'alternativeComparisons.consistency'
        ]);

        $this->verifyNetworkStructure($analysis);

        foreach ($analysis->networkStructure->clusters as $cluster) {
            $key = 'cluster_' . $cluster->id;
            $elementsMap[$key] = $idx;
            $nodeList[$idx] = [
                'id' => $cluster->id, 
                'type' => 'cluster', 
                'name' => $cluster->name,
                'cluster_key' => $key
            ];
            $idx++;
        }
        
        foreach ($analysis->networkStructure->elements as $element) {
            $key = 'element_' . $element->id;
            $elementsMap[$key] = $idx;
            $nodeList[$idx] = [
                'id' => $element->id, 
                'type' => 'element', 
                'name' => $element->name, 
                'cluster_key' => $element->anp_cluster_id ? 'cluster_' . $element->anp_cluster_id : null
            ];
            $idx++;
        }
        foreach ($analysis->candidates as $candidate) {
            $key = 'alternative_' . $candidate->id;
            $elementsMap[$key] = $idx;
            $nodeList[$idx] = [
                'id' => $candidate->id, 
                'type' => 'alternative', 
                'name' => $candidate->name, 
                'cluster_key' => null
            ];
            $idx++;
        }
        $criteriaWeights = $this->getValidPriorityVectors($analysis->criteriaComparisons);
        $interdependencyWeights = $this->getValidPriorityVectors($analysis->interdependencyComparisons);
        $alternativeScores = $this->getValidPriorityVectors($analysis->alternativeComparisons);

        $this->checkInnerDependenceComparisons($analysis, $criteriaWeights);

        return compact('elementsMap', 'nodeList', 'criteriaWeights', 'interdependencyWeights', 'alternativeScores');
    }

    private function verifyNetworkStructure(AnpAnalysis $analysis): void
    {
        $currentStructure = $analysis->networkStructure;
        
        if (!$currentStructure->is_frozen) {
            throw new \Exception('Network structure must be frozen before calculation');
        }
        
        $lastSnapshot = AnpStructureSnapshot::where('anp_analysis_id', $analysis->id)
            ->where('snapshot_type', 'proceed_to_comparison')
            ->latest()
            ->first();
            
        if ($lastSnapshot) {
            Log::info('Verifying network structure integrity', [
                'analysis_id' => $analysis->id,
                'snapshot_date' => $lastSnapshot->created_at
            ]);
        }
    }

    private function checkInnerDependenceComparisons(AnpAnalysis $analysis, array $criteriaWeights): void
    {
        $clusters = $analysis->networkStructure->clusters()->withCount('elements')->get();
        
        foreach ($clusters as $cluster) {
            if ($cluster->elements_count > 1) {
                $hasComparison = collect($criteriaWeights)->contains(function ($comp) use ($cluster) {
                    return $comp->control_criterionable_type === AnpCluster::class 
                        && $comp->control_criterionable_id == $cluster->id;
                });
                
                if (!$hasComparison) {
                    Log::warning("[ANP ID:{$analysis->id}] Missing inner dependence comparison for cluster '{$cluster->name}' with {$cluster->elements_count} elements");
                }
            }
        }
    }

    private function getValidPriorityVectors($comparisons): array
    {
        $vectors = [];
        
        foreach ($comparisons as $comparison) {
            if ($comparison->consistency && $comparison->consistency->is_consistent) {
                $vectors[] = $comparison;
            } else {
                Log::warning("Skipping inconsistent/unprocessed comparison.", [
                    'comparison_id' => $comparison->id, 
                    'type' => get_class($comparison),
                    'has_consistency' => $comparison->consistency ? 'yes' : 'no',
                    'is_consistent' => $comparison->consistency ? $comparison->consistency->is_consistent : 'N/A'
                ]);
            }
        }
        
        return $vectors;
    }

    private function buildUnweightedSupermatrix(AnpAnalysis $analysis, array $priorityData): array
    {
        $elementsMap = $priorityData['elementsMap'];
        $nodeList = $priorityData['nodeList'];
        $numNodes = count($elementsMap);
        $supermatrix = array_fill(0, $numNodes, array_fill(0, $numNodes, 0.0));

        Log::info("[ANP ID:{$analysis->id}] Building unweighted supermatrix with {$numNodes} nodes");
        
        $connectionCount = 0;

        foreach ($nodeList as $idx => $node) {
            if ($node['type'] === 'element' && $node['cluster_key']) {
                $clusterIdx = $elementsMap[$node['cluster_key']] ?? null;
                if ($clusterIdx !== null) {
                    $supermatrix[$idx][$clusterIdx] = 1.0;
                    $connectionCount++;
                    Log::info("[ANP ID:{$analysis->id}] Connected element '{$node['name']}' to its cluster");
                }
            }
        }

        $alternativeIndices = [];
        $elementIndices = [];
        $clusterIndices = [];
        
        foreach ($nodeList as $idx => $node) {
            switch ($node['type']) {
                case 'alternative':
                    $alternativeIndices[] = $idx;
                    break;
                case 'element':
                    $elementIndices[] = $idx;
                    break;
                case 'cluster':
                    $clusterIndices[] = $idx;
                    break;
            }
        }
        
        if (!empty($alternativeIndices) && !empty($clusterIndices)) {
            $feedbackWeight = 1.0 / count($alternativeIndices);
            foreach ($clusterIndices as $clusterIdx) {
                foreach ($alternativeIndices as $altIdx) {
                    $supermatrix[$altIdx][$clusterIdx] = $feedbackWeight;
                    $connectionCount++;
                }
            }
            
            $returnWeight = 1.0 / count($elementIndices);
            foreach ($alternativeIndices as $altIdx) {
                foreach ($elementIndices as $elemIdx) {
                    $supermatrix[$elemIdx][$altIdx] = $returnWeight;
                    $connectionCount++;
                }
            }
            
            Log::info("[ANP ID:{$analysis->id}] Added bidirectional feedback loops");
        }

        foreach ($priorityData['interdependencyWeights'] as $comp) {
            if(!$comp->dependency) continue;
            $targetable = $comp->dependency->targetable;
            $targetKey = $this->getNodeKeyPrefix(get_class($targetable)) . $targetable->id;
            $colIdx = $elementsMap[$targetKey] ?? null;
            if ($colIdx === null) continue;

            foreach ($comp->priority_vector as $sourceId => $weight) {
                $sourceKey = 'element_' . $sourceId;
                $rowIdx = $elementsMap[$sourceKey] ?? null;
                if ($rowIdx !== null && $weight > 0) {
                    $supermatrix[$rowIdx][$colIdx] = $weight;
                    $connectionCount++;
                }
            }
        }

        foreach ($priorityData['alternativeScores'] as $comp) {
            $criterionKey = 'element_' . $comp->anp_element_id;
            $colIdx = $elementsMap[$criterionKey] ?? null;
            if ($colIdx === null) continue;

            foreach ($comp->priority_vector as $candidateId => $weight) {
                $alternativeKey = 'alternative_' . $candidateId;
                $rowIdx = $elementsMap[$alternativeKey] ?? null;
                if ($rowIdx !== null && $weight > 0) {
                    $supermatrix[$rowIdx][$colIdx] = $weight;
                    $connectionCount++;
                }
            }
        }

        foreach ($priorityData['criteriaWeights'] as $comp) {
            if ($comp->control_criterionable_type === AnpCluster::class) {
                $clusterKey = 'cluster_' . $comp->control_criterionable_id;
                $colIdx = $elementsMap[$clusterKey] ?? null;
                if ($colIdx === null) continue;

                foreach ($comp->priority_vector as $elementId => $weight) {
                    $elementKey = 'element_' . $elementId;
                    $rowIdx = $elementsMap[$elementKey] ?? null;
                    if ($rowIdx !== null && $weight > 0) {
                        $supermatrix[$rowIdx][$colIdx] = $weight;
                        $connectionCount++;
                    }
                }
            }
        }

        Log::info("[ANP ID:{$analysis->id}] Total connections in unweighted supermatrix: {$connectionCount}");
        
        $dampingFactor = 0.15; 
        for ($i = 0; $i < $numNodes; $i++) {
            $colSum = array_sum(array_column($supermatrix, $i));
            if ($colSum < 1e-9) { 
                $supermatrix[$i][$i] = 1.0;
                Log::info("[ANP ID:{$analysis->id}] Added self-loop to node {$i} ({$nodeList[$i]['name']})");
            }
        }

        for ($j = 0; $j < $numNodes; $j++) {
            $colSum = 0;
            for ($i = 0; $i < $numNodes; $i++) {
                $colSum += $supermatrix[$i][$j];
            }
            
            if ($colSum > 0) {
                for ($i = 0; $i < $numNodes; $i++) {
                    $supermatrix[$i][$j] = (1 - $dampingFactor) * ($supermatrix[$i][$j] / $colSum) 
                                        + $dampingFactor / $numNodes;
                }
            }
        }
        
        $this->validateSupermatrix($supermatrix, $nodeList, $analysis->id);
        
        return $supermatrix;
    }

    private function validateSupermatrix(array $matrix, array $nodeList, int $analysisId): void
    {
        $n = count($matrix);
        $issues = [];
        
        for ($i = 0; $i < $n; $i++) {
            $rowSum = array_sum($matrix[$i]);
            if ($rowSum < 1e-9) {
                $issues[] = "Zero row at {$nodeList[$i]['name']} (index {$i})";
            }
        }
        
        for ($j = 0; $j < $n; $j++) {
            $colSum = 0;
            for ($i = 0; $i < $n; $i++) {
                $colSum += $matrix[$i][$j];
            }
            if ($colSum < 1e-9) {
                $issues[] = "Zero column at {$nodeList[$j]['name']} (index {$j})";
            } elseif (abs($colSum - 1.0) > 1e-6) {
                $issues[] = "Non-stochastic column at {$nodeList[$j]['name']} (sum = {$colSum})";
            }
        }
        
        if (!empty($issues)) {
            Log::warning("[ANP ID:{$analysisId}] Supermatrix validation issues: " . implode('; ', $issues));
        } else {
            Log::info("[ANP ID:{$analysisId}] Supermatrix validation passed");
        }
    }

    private function buildWeightedSupermatrix(array $unweightedSupermatrix, AnpAnalysis $analysis, array $priorityData): array
    {
        $nodeList = $priorityData['nodeList'];
        $numNodes = count($nodeList);
        $weightedSupermatrix = $unweightedSupermatrix;

        $clusterWeights = [];
        foreach ($priorityData['criteriaWeights'] as $comp) {
            if ($comp->control_criterionable_type === null && $comp->compared_elements_type === AnpCluster::class) {
                foreach ($comp->priority_vector as $clusterId => $weight) {
                    $clusterWeights['cluster_' . $clusterId] = $weight;
                }
                Log::info("[ANP ID:{$analysis->id}] Found cluster weights vs goal:", $clusterWeights);
            }
        }

        if (empty($clusterWeights)) {
            Log::warning("[ANP ID:{$analysis->id}] Bobot Cluster utama (vs Goal) tidak ditemukan. Menggunakan bobot uniform.");
            
            $clusterCount = 0;
            foreach ($nodeList as $node) {
                if ($node['type'] === 'cluster') {
                    $clusterCount++;
                }
            }
            
            if ($clusterCount > 0) {
                $uniformWeight = 1.0 / $clusterCount;
                foreach ($nodeList as $node) {
                    if ($node['type'] === 'cluster') {
                        $clusterWeights[$node['cluster_key']] = $uniformWeight;
                    }
                }
                Log::info("[ANP ID:{$analysis->id}] Using uniform cluster weights:", $clusterWeights);
            }
        }

        for ($j = 0; $j < $numNodes; $j++) {
            $colNode = $nodeList[$j];
            $clusterKey = $colNode['cluster_key'] ?? null;

            if ($clusterKey && isset($clusterWeights[$clusterKey])) {
                $weight = $clusterWeights[$clusterKey];
                for ($i = 0; $i < $numNodes; $i++) {
                    $weightedSupermatrix[$i][$j] *= $weight;
                }
            }
        }

        $nonZeroCount = 0;
        for ($i = 0; $i < $numNodes; $i++) {
            for ($j = 0; $j < $numNodes; $j++) {
                if ($weightedSupermatrix[$i][$j] > 1e-9) {
                    $nonZeroCount++;
                }
            }
        }
        Log::info("[ANP ID:{$analysis->id}] Weighted matrix has {$nonZeroCount} non-zero entries before normalization");

        $columnSums = array_fill(0, $numNodes, 0.0);
        for ($j = 0; $j < $numNodes; $j++) {
            for ($i = 0; $i < $numNodes; $i++) {
                $columnSums[$j] += $weightedSupermatrix[$i][$j];
            }
        }

        $zeroColumns = [];
        for ($j = 0; $j < $numNodes; $j++) {
            if ($columnSums[$j] <= 1e-9) {
                $zeroColumns[] = $nodeList[$j]['name'] . " (idx:{$j})";
            }
        }
        if (!empty($zeroColumns)) {
            Log::warning("[ANP ID:{$analysis->id}] Columns with zero sum (will remain zero after normalization): " . implode(', ', $zeroColumns));
        }

        for ($j = 0; $j < $numNodes; $j++) {
            if ($columnSums[$j] > 0) {
                for ($i = 0; $i < $numNodes; $i++) {
                    $weightedSupermatrix[$i][$j] /= $columnSums[$j];
                }
            }
        }

        Log::info("[ANP ID:{$analysis->id}] Sample of weighted matrix after normalization:");
        for ($i = 0; $i < min(5, $numNodes); $i++) {
            $row = [];
            for ($j = 0; $j < min(5, $numNodes); $j++) {
                $row[] = round($weightedSupermatrix[$i][$j], 4);
            }
            Log::info("Row {$i} ({$nodeList[$i]['name']}): " . implode(', ', $row));
        }

        return $weightedSupermatrix;
    }
    
    private function calculateLimitSupermatrix(array $matrix): array
    {
        if (empty($matrix)) {
            throw new Exception('Matrix for limit calculation is empty.');
        }
        
        $n = count($matrix);
        $currentMatrix = $matrix;
        $tolerance = $this->convergenceTolerance;
        $maxIter = $this->maxIterations;
        
        $matrixHistory = [];
        $checkCycleEvery = 5;
        
        for ($iteration = 0; $iteration < $maxIter; $iteration++) {
            $nextMatrix = $this->multiplyMatrix($currentMatrix, $currentMatrix);
            
            $diff = 0;
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $diff += abs($nextMatrix[$i][$j] - $currentMatrix[$i][$j]);
                }
            }
            
            if ($diff < $tolerance) {
                Log::info("Limit supermatrix converged after " . ($iteration + 1) . " iterations.");
                return $nextMatrix;
            }
            
            if ($iteration > 10 && $iteration % $checkCycleEvery == 0) {
                foreach ($matrixHistory as $histIdx => $histMatrix) {
                    $histDiff = 0;
                    for ($i = 0; $i < $n; $i++) {
                        for ($j = 0; $j < $n; $j++) {
                            $histDiff += abs($nextMatrix[$i][$j] - $histMatrix[$i][$j]);
                        }
                    }
                    
                    if ($histDiff < $tolerance) {
                        Log::warning("Detected cycle at iteration {$iteration}. Using Cesaro sum.");
                        return $this->cesaroSum($matrixHistory, $nextMatrix);
                    }
                }
                
                $matrixHistory[] = $currentMatrix;
                if (count($matrixHistory) > 5) {
                    array_shift($matrixHistory);
                }
            }
            
            $currentMatrix = $nextMatrix;
        }
        
        Log::warning("Limit supermatrix did not converge after {$maxIter} iterations. Using final state.");
        return $currentMatrix;
    }

    private function cesaroSum(array $matrices, array $currentMatrix): array
    {
        $n = count($currentMatrix);
        $sumMatrix = array_fill(0, $n, array_fill(0, $n, 0.0));
        
        $allMatrices = array_merge($matrices, [$currentMatrix]);
        $count = count($allMatrices);
        
        foreach ($allMatrices as $matrix) {
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $sumMatrix[$i][$j] += $matrix[$i][$j];
                }
            }
        }
        
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $sumMatrix[$i][$j] /= $count;
            }
        }
        
        return $sumMatrix;
    }
    
    private function extractFinalScores(array $limitSupermatrix, array $priorityData): array
    {
        $nodeList = $priorityData['nodeList'];
        $rawScores = [];

        Log::info('[extractFinalScores] Extracting scores from limit supermatrix');

        $alternativeData = [];
        foreach ($nodeList as $idx => $nodeInfo) {
            if ($nodeInfo['type'] === 'alternative') {
                $alternativeData[$idx] = $nodeInfo;
            }
        }
        
        if (empty($alternativeData)) {
            Log::error('[extractFinalScores] No alternatives found in node list!');
            return [];
        }

        foreach ($alternativeData as $altIdx => $altInfo) {
            $rowSum = 0;
            $nonZeroCount = 0;
            
            for ($j = 0; $j < count($limitSupermatrix[0]); $j++) {
                $value = $limitSupermatrix[$altIdx][$j] ?? 0;
                $rowSum += $value;
                if ($value > 1e-9) {
                    $nonZeroCount++;
                }
            }
            
            if ($rowSum > 1.5) {
                $rowSum = $rowSum / $nonZeroCount;
            }
            
            $rawScores[$altInfo['id']] = max(0, $rowSum);
            Log::info("[extractFinalScores] Alternative '{$altInfo['name']}' (id: {$altInfo['id']}): raw_score = {$rowSum}");
        }

        $totalScore = array_sum($rawScores);
        Log::info('[extractFinalScores] Total score before normalization: ' . $totalScore);
        
        if ($totalScore > 1e-9) {
            foreach ($rawScores as $userId => &$score) {
                $score = $score / $totalScore;
            }
        } else {
            Log::warning('[extractFinalScores] Total score is zero. Using equal weights.');
            $equalWeight = 1.0 / count($rawScores);
            foreach ($rawScores as $userId => &$score) {
                $score = $equalWeight;
            }
        }

        arsort($rawScores);
        $rankedScores = [];
        $rank = 1;
        $prevScore = null;
        
        foreach ($rawScores as $userId => $score) {
            if ($prevScore !== null && abs($score - $prevScore) < 1e-6) {
                Log::warning("[extractFinalScores] Nearly identical scores detected for rank {$rank}");
            }
            
            $rankedScores[] = [
                'user_id' => $userId, 
                'score' => round($score, 6), 
                'rank' => $rank++
            ];
            
            $prevScore = $score;
        }

        $uniqueScores = count(array_unique(array_column($rankedScores, 'score')));
        if ($uniqueScores === 1) {
            Log::error('[extractFinalScores] All candidates have identical scores. Check network structure and comparisons.');
            
            $this->applyTieBreaking($rankedScores, $priorityData);
        } else {
            Log::info('[extractFinalScores] Successfully differentiated ' . $uniqueScores . ' unique score levels');
        }

        return $rankedScores;
    }

    private function applyTieBreaking(array &$rankedScores, array $priorityData): void
    {
        Log::info('[applyTieBreaking] Applying tie-breaking logic for identical scores');
        
        $alternativeScores = [];
        foreach ($priorityData['alternativeScores'] as $comp) {
            foreach ($comp->priority_vector as $candidateId => $weight) {
                if (!isset($alternativeScores[$candidateId])) {
                    $alternativeScores[$candidateId] = [];
                }
                $alternativeScores[$candidateId][] = $weight;
            }
        }
        
        $avgScores = [];
        foreach ($alternativeScores as $candidateId => $scores) {
            $avgScores[$candidateId] = array_sum($scores) / count($scores);
        }
        
        usort($rankedScores, function($a, $b) use ($avgScores) {
            if (abs($a['score'] - $b['score']) > 1e-6) {
                return $b['score'] <=> $a['score'];
            }
            
            $scoreA = $avgScores[$a['user_id']] ?? 0;
            $scoreB = $avgScores[$b['user_id']] ?? 0;
            
            return $scoreB <=> $scoreA;
        });
        
        foreach ($rankedScores as $idx => &$score) {
            $score['rank'] = $idx + 1;
        }
    }
    
    private function saveResults(AnpAnalysis $analysis, array $finalScores): void
    {
        $analysis->results()->delete();
        foreach ($finalScores as $scoreData) {
            $analysis->results()->create($scoreData);
        }
    }

    private function multiplyMatrix(array $matrixA, array $matrixB): array
    {
        $rowsA = count($matrixA);
        $colsA = count($matrixA[0] ?? []);
        $colsB = count($matrixB[0] ?? []);
        if ($colsA === 0 || $colsB === 0) return [];

        $result = array_fill(0, $rowsA, array_fill(0, $colsB, 0.0));

        for ($i = 0; $i < $rowsA; $i++) {
            for ($j = 0; $j < $colsB; $j++) {
                for ($k = 0; $k < $colsA; $k++) {
                    $result[$i][$j] += $matrixA[$i][$k] * $matrixB[$k][$j];
                }
            }
        }
        return $result;
    }
    
    private function getNodeKeyPrefix(string $modelClass): string
    {
        return match ($modelClass) {
            AnpElement::class => 'element_',
            AnpCluster::class => 'cluster_',
            User::class => 'alternative_',
            default => 'node_',
        };
    }
}