<?php

namespace App\Services;

use App\Models\AnpAnalysis;
use App\Models\AnpCluster;
use App\Models\AnpDependency;
use App\Models\AnpElement;
use App\Models\User;
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
                
                // Debug: Log cluster weights
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
        if ($n === 0) {
            return ['priority_vector' => [], 'is_consistent' => true, 'consistency_ratio' => 0.0, 'lambda_max' => 0, 'consistency_index' => 0, 'random_index' => 0];
        }
        if ($n === 1) {
            $key = array_key_first($matrix);
            return ['priority_vector' => [$key => 1.0], 'is_consistent' => true, 'consistency_ratio' => 0.0, 'lambda_max' => 1, 'consistency_index' => 0, 'random_index' => 0];
        }

        $keys = array_keys($matrix);
        $numericMatrix = array_values(array_map('array_values', $matrix));
        
        $columnSums = array_fill(0, $n, 0);
        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $columnSums[$j] += $numericMatrix[$i][$j];
            }
        }

        $priorityVectorNumeric = array_fill(0, $n, 0);
        for ($i = 0; $i < $n; $i++) {
            $rowSum = 0;
            for ($j = 0; $j < $n; $j++) {
                $normalizedValue = $columnSums[$j] > 0 ? $numericMatrix[$i][$j] / $columnSums[$j] : 0;
                $rowSum += $normalizedValue;
            }
            $priorityVectorNumeric[$i] = $rowSum / $n;
        }

        $weightedSumVector = array_fill(0, $n, 0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $weightedSumVector[$i] += $numericMatrix[$i][$j] * $priorityVectorNumeric[$j];
            }
        }
        
        $lambdaMax = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($priorityVectorNumeric[$i] > 1e-9) {
                $lambdaMax += $weightedSumVector[$i] / $priorityVectorNumeric[$i];
            }
        }
        $lambdaMax = ($n > 0) ? $lambdaMax / $n : 0;

        $ci = ($n <= 2) ? 0 : ($lambdaMax - $n) / ($n - 1);
        $ri = $this->randomIndices[$n] ?? end($this->randomIndices);
        $cr = ($ri > 0) ? $ci / $ri : 0;

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
        
        // Eager load semua relasi yang dibutuhkan
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

        // Verify network structure
        $this->verifyNetworkStructure($analysis);

        // Map clusters
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
        
        // Map elements
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
        
        // Map alternatives
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

        // Get valid priority vectors dengan eager loading
        $criteriaWeights = $this->getValidPriorityVectors($analysis->criteriaComparisons);
        $interdependencyWeights = $this->getValidPriorityVectors($analysis->interdependencyComparisons);
        $alternativeScores = $this->getValidPriorityVectors($analysis->alternativeComparisons);

        // Check for missing inner dependence comparisons
        $this->checkInnerDependenceComparisons($analysis, $criteriaWeights);

        return compact('elementsMap', 'nodeList', 'criteriaWeights', 'interdependencyWeights', 'alternativeScores');
    }

    private function verifyNetworkStructure(AnpAnalysis $analysis): void
    {
        $clusters = $analysis->networkStructure->clusters;
        $elements = $analysis->networkStructure->elements;
        $candidates = $analysis->candidates;

        Log::info("[ANP ID:{$analysis->id}] Network structure:", [
            'clusters' => $clusters->count(),
            'elements' => $elements->count(),
            'candidates' => $candidates->count()
        ]);

        // Check if all elements belong to clusters
        $orphanElements = $elements->filter(fn($el) => !$el->anp_cluster_id);
        if ($orphanElements->isNotEmpty()) {
            Log::warning("[ANP ID:{$analysis->id}] Elements without cluster: " . $orphanElements->pluck('name')->implode(', '));
        }

        // Check cluster-element distribution
        foreach ($clusters as $cluster) {
            $elementCount = $elements->where('anp_cluster_id', $cluster->id)->count();
            Log::info("[ANP ID:{$analysis->id}] Cluster '{$cluster->name}' has {$elementCount} elements");
        }
    }

    private function checkInnerDependenceComparisons(AnpAnalysis $analysis, array $criteriaWeights): void
    {
        // Check if we have inner dependence comparisons for each cluster with multiple elements
        $clusters = $analysis->networkStructure->clusters()->withCount('elements')->get();
        
        foreach ($clusters as $cluster) {
            if ($cluster->elements_count > 1) {
                // Check if we have comparison for this cluster
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

    Private function buildUnweightedSupermatrix(AnpAnalysis $analysis, array $priorityData): array
    {
        $elementsMap = $priorityData['elementsMap'];
        $nodeList = $priorityData['nodeList'];
        $numNodes = count($elementsMap);
        $supermatrix = array_fill(0, $numNodes, array_fill(0, $numNodes, 0.0));

        Log::info("[ANP ID:{$analysis->id}] Building unweighted supermatrix with {$numNodes} nodes");
        
        $connectionCount = 0;

        // 1. Add element-to-cluster connections
        foreach ($nodeList as $idx => $node) {
            if ($node['type'] === 'element' && $node['cluster_key']) {
                $clusterIdx = $elementsMap[$node['cluster_key']] ?? null;
                if ($clusterIdx !== null) {
                    $supermatrix[$idx][$clusterIdx] = 1.0;
                    $connectionCount++;
                    Log::info("[ANP ID:{$analysis->id}] Connected element '{$node['name']}' (idx:{$idx}) to cluster (idx:{$clusterIdx})");
                }
            }
        }

        // 2. CRITICAL FIX: Add feedback from clusters to alternatives
        // This creates the necessary feedback loop for ANP to work properly
        $alternativeIndices = [];
        $clusterIndices = [];
        
        foreach ($nodeList as $idx => $node) {
            if ($node['type'] === 'alternative') {
                $alternativeIndices[] = $idx;
            } elseif ($node['type'] === 'cluster') {
                $clusterIndices[] = $idx;
            }
        }
        
        // Create uniform feedback from each cluster to all alternatives
        if (!empty($alternativeIndices) && !empty($clusterIndices)) {
            $feedbackWeight = 1.0 / count($clusterIndices);
            
            foreach ($alternativeIndices as $altIdx) {
                foreach ($clusterIndices as $clusterIdx) {
                    $supermatrix[$clusterIdx][$altIdx] = $feedbackWeight;
                    $connectionCount++;
                }
            }
            
            Log::info("[ANP ID:{$analysis->id}] Added feedback connections from " . count($clusterIndices) . " clusters to " . count($alternativeIndices) . " alternatives");
        }

        // 3. Interdependency weights
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

        // 4. Alternative scores (alternatives influence elements)
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
                    Log::info("[ANP ID:{$analysis->id}] Alternative {$candidateId} → Element {$comp->anp_element_id}: weight = {$weight}");
                }
            }
        }
        
        // 5. Criteria weights (inner dependence)
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
        
        // Pre-normalize the unweighted matrix to ensure column stochasticity
        for ($j = 0; $j < $numNodes; $j++) {
            $colSum = 0;
            for ($i = 0; $i < $numNodes; $i++) {
                $colSum += $supermatrix[$i][$j];
            }
            
            if ($colSum > 0) {
                for ($i = 0; $i < $numNodes; $i++) {
                    $supermatrix[$i][$j] /= $colSum;
                }
            }
        }
        
        // Debug check
        $zeroRows = [];
        $zeroCols = [];
        for ($i = 0; $i < $numNodes; $i++) {
            $rowSum = array_sum($supermatrix[$i]);
            $colSum = array_sum(array_column($supermatrix, $i));
            
            if ($rowSum == 0) {
                $zeroRows[] = $nodeList[$i]['name'] . " (idx:{$i})";
            }
            if ($colSum == 0) {
                $zeroCols[] = $nodeList[$i]['name'] . " (idx:{$i})";
            }
        }
        
        if (!empty($zeroRows)) {
            Log::warning("[ANP ID:{$analysis->id}] Nodes with zero outgoing: " . implode(', ', $zeroRows));
        }
        if (!empty($zeroCols)) {
            Log::warning("[ANP ID:{$analysis->id}] Nodes with zero incoming: " . implode(', ', $zeroCols));
        }

        return $supermatrix;
    }

    private function buildWeightedSupermatrix(array $unweightedSupermatrix, AnpAnalysis $analysis, array $priorityData): array
    {
        $nodeList = $priorityData['nodeList'];
        $numNodes = count($nodeList);
        $weightedSupermatrix = $unweightedSupermatrix;

        // Extract cluster weights vs goal
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
            
            // Fallback: berikan bobot uniform untuk semua cluster
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

        // Apply cluster weights
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

        // Debug: Check matrix before normalization
        $nonZeroCount = 0;
        for ($i = 0; $i < $numNodes; $i++) {
            for ($j = 0; $j < $numNodes; $j++) {
                if ($weightedSupermatrix[$i][$j] > 1e-9) {
                    $nonZeroCount++;
                }
            }
        }
        Log::info("[ANP ID:{$analysis->id}] Weighted matrix has {$nonZeroCount} non-zero entries before normalization");

        // Normalize columns
        $columnSums = array_fill(0, $numNodes, 0.0);
        for ($j = 0; $j < $numNodes; $j++) {
            for ($i = 0; $i < $numNodes; $i++) {
                $columnSums[$j] += $weightedSupermatrix[$i][$j];
            }
        }

        // Debug: Log column sums
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

        // Debug: Sample the weighted matrix
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
        if (empty($matrix)) throw new Exception('Matrix for limit calculation is empty.');
        
        $limitMatrix = $matrix;
        for ($i = 0; $i < $this->maxIterations; $i++) {
            $poweredMatrix = $this->multiplyMatrix($limitMatrix, $limitMatrix);
            
            $diff = 0;
            $n = count($matrix);
            for ($r = 0; $r < $n; $r++) {
                for ($c = 0; $c < $n; $c++) {
                    $diff += abs($poweredMatrix[$r][$c] - $limitMatrix[$r][$c]);
                }
            }

            $limitMatrix = $poweredMatrix;
            if ($diff < $this->convergenceTolerance) {
                Log::info("Limit supermatrix converged after " . ($i + 1) . " iterations.");
                return $limitMatrix;
            }
        }

        Log::warning("Limit supermatrix did not converge after {$this->maxIterations} iterations.");
        return $limitMatrix;
    }
    
    private function extractFinalScores(array $limitSupermatrix, array $priorityData): array
    {
        $nodeList = $priorityData['nodeList'];
        $rawScores = [];

        Log::info('[extractFinalScores] Extracting scores from limit supermatrix');

        // In ANP, we extract the limiting priorities for alternatives
        // These are found in the columns of the limit matrix corresponding to alternatives
        
        // First, find all alternative indices
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

        // For each alternative, take the first non-zero column value
        // In a converged limit matrix, all columns should be identical
        foreach ($alternativeData as $altIdx => $altInfo) {
            // Try multiple extraction methods
            $score = 0;
            
            // Method 1: First column value
            if (isset($limitSupermatrix[$altIdx][0])) {
                $score = $limitSupermatrix[$altIdx][0];
            }
            
            // Method 2: If zero, try average of row
            if ($score <= 1e-9) {
                $rowSum = array_sum($limitSupermatrix[$altIdx] ?? []);
                $rowCount = count($limitSupermatrix[$altIdx] ?? []);
                $score = $rowCount > 0 ? $rowSum / $rowCount : 0;
            }
            
            // Method 3: If still zero, try column values for this alternative
            if ($score <= 1e-9 && isset($limitSupermatrix[0][$altIdx])) {
                $colSum = 0;
                for ($i = 0; $i < count($limitSupermatrix); $i++) {
                    $colSum += $limitSupermatrix[$i][$altIdx] ?? 0;
                }
                $score = $colSum;
            }
            
            $rawScores[$altInfo['id']] = $score;
            Log::info("[extractFinalScores] Alternative '{$altInfo['name']}' (id: {$altInfo['id']}): score = {$score}");
        }

        $totalScore = array_sum($rawScores);
        Log::info('[extractFinalScores] Total score: ' . $totalScore);
        
        if ($totalScore <= 1e-9) {
            // Last resort: equal weights
            Log::warning('[extractFinalScores] All methods failed. Using equal weights.');
            $equalWeight = 1.0 / count($rawScores);
            foreach ($rawScores as $userId => &$score) {
                $score = $equalWeight;
            }
            $totalScore = 1.0;
        }

        // Normalize and rank
        $finalScores = [];
        foreach ($rawScores as $userId => $score) {
            $finalScores[$userId] = $score / $totalScore;
        }
        
        arsort($finalScores);

        $rankedScores = [];
        $rank = 1;
        foreach ($finalScores as $userId => $score) {
            $rankedScores[] = [
                'user_id' => $userId, 
                'score' => round($score, 6), 
                'rank' => $rank++
            ];
        }

        return $rankedScores;
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