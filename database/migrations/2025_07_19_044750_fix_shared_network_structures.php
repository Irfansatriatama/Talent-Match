<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixSharedNetworkStructures extends Migration
{
    public function up()
    {
        // Find all analyses still using shared structure ID 1
        $sharedAnalyses = DB::table('anp_analyses')
            ->where('anp_network_structure_id', 1)
            ->whereNull('deleted_at')
            ->get();
            
        foreach ($sharedAnalyses as $analysis) {
            Log::info("Fixing shared structure for analysis #{$analysis->id}");
            
            // Get original structure
            $originalStructure = DB::table('anp_network_structures')
                ->where('id', 1)
                ->first();
                
            if (!$originalStructure) continue;
            
            // Create unique structure for this analysis
            $newStructureId = DB::table('anp_network_structures')->insertGetId([
                'name' => "Network untuk {$analysis->name} (Analisis #{$analysis->id})",
                'description' => $originalStructure->description . " (Isolated copy)",
                'is_frozen' => $originalStructure->is_frozen,
                'version' => $originalStructure->version,
                'original_analysis_id' => $analysis->id,
                'is_template' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Copy all related data
            $this->copyNetworkData(1, $newStructureId);
            
            // Update analysis
            DB::table('anp_analyses')
                ->where('id', $analysis->id)
                ->update([
                    'anp_network_structure_id' => $newStructureId,
                    'updated_at' => now()
                ]);
        }
        
        // Mark structure ID 1 as template
        DB::table('anp_network_structures')
            ->where('id', 1)
            ->update([
                'is_template' => true,
                'name' => 'TEMPLATE - DO NOT USE DIRECTLY',
                'updated_at' => now()
            ]);
    }
    
    private function copyNetworkData($oldStructureId, $newStructureId)
    {
        // Copy clusters
        $clusters = DB::table('anp_clusters')
            ->where('anp_network_structure_id', $oldStructureId)
            ->whereNull('deleted_at')
            ->get();
            
        $clusterMapping = [];
        foreach ($clusters as $cluster) {
            $newClusterId = DB::table('anp_clusters')->insertGetId([
                'anp_network_structure_id' => $newStructureId,
                'name' => $cluster->name,
                'description' => $cluster->description,
                'created_at' => $cluster->created_at,
                'updated_at' => now(),
                'deleted_at' => null
            ]);
            $clusterMapping[$cluster->id] = $newClusterId;
        }
        
        // Copy elements
        $elements = DB::table('anp_elements')
            ->where('anp_network_structure_id', $oldStructureId)
            ->whereNull('deleted_at')
            ->get();
            
        $elementMapping = [];
        foreach ($elements as $element) {
            $newClusterId = $element->anp_cluster_id ? 
                ($clusterMapping[$element->anp_cluster_id] ?? null) : null;
                
            $newElementId = DB::table('anp_elements')->insertGetId([
                'anp_network_structure_id' => $newStructureId,
                'anp_cluster_id' => $newClusterId,
                'name' => $element->name,
                'description' => $element->description,
                'created_at' => $element->created_at,
                'updated_at' => now(),
                'deleted_at' => null
            ]);
            $elementMapping[$element->id] = $newElementId;
        }
        
        // Copy dependencies
        $dependencies = DB::table('anp_dependencies')
            ->where('anp_network_structure_id', $oldStructureId)
            ->whereNull('deleted_at')
            ->get();
            
        foreach ($dependencies as $dep) {
            $newSourceId = $this->mapId($dep->sourceable_type, $dep->sourceable_id, $clusterMapping, $elementMapping);
            $newTargetId = $this->mapId($dep->targetable_type, $dep->targetable_id, $clusterMapping, $elementMapping);
            
            if ($newSourceId && $newTargetId) {
                DB::table('anp_dependencies')->insert([
                    'anp_network_structure_id' => $newStructureId,
                    'sourceable_type' => $dep->sourceable_type,
                    'sourceable_id' => $newSourceId,
                    'targetable_type' => $dep->targetable_type,
                    'targetable_id' => $newTargetId,
                    'description' => $dep->description,
                    'created_at' => $dep->created_at,
                    'updated_at' => now(),
                    'deleted_at' => null
                ]);
            }
        }
    }
    
    private function mapId($type, $id, $clusterMapping, $elementMapping)
    {
        if ($type == 'App\\Models\\AnpCluster') {
            return $clusterMapping[$id] ?? null;
        } elseif ($type == 'App\\Models\\AnpElement') {
            return $elementMapping[$id] ?? null;
        }
        return null;
    }
    
    public function down()
    {
        // This migration is not reversible safely
        Log::warning('Rollback of fix_shared_network_structures is not recommended');
    }
}