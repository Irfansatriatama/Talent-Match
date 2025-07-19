<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixAnpNetworkIsolation extends Migration
{
    public function up()
    {
        // 1. Add tracking column to network structures
        Schema::table('anp_network_structures', function (Blueprint $table) {
            $table->unsignedBigInteger('original_analysis_id')->nullable()->after('id');
            $table->boolean('is_template')->default(false)->after('is_frozen');
            $table->index('original_analysis_id');
        });

        // 2. Create new isolated structures for existing analyses
        $analyses = DB::table('anp_analyses')
            ->whereNotNull('anp_network_structure_id')
            ->get();

        foreach ($analyses as $analysis) {
            // Skip if already has unique structure
            $sharedCount = DB::table('anp_analyses')
                ->where('anp_network_structure_id', $analysis->anp_network_structure_id)
                ->count();
                
            if ($sharedCount <= 1) {
                // Update tracking
                DB::table('anp_network_structures')
                    ->where('id', $analysis->anp_network_structure_id)
                    ->update(['original_analysis_id' => $analysis->id]);
                continue;
            }

            Log::info("Creating isolated structure for analysis #{$analysis->id}");

            // Get original structure
            $originalStructure = DB::table('anp_network_structures')
                ->where('id', $analysis->anp_network_structure_id)
                ->first();

            // Create new structure
            $newStructureId = DB::table('anp_network_structures')->insertGetId([
                'name' => "Network untuk {$analysis->name} (ID: {$analysis->id})",
                'description' => $originalStructure->description . " (Isolated copy)",
                'is_frozen' => $originalStructure->is_frozen,
                'version' => $originalStructure->version,
                'original_analysis_id' => $analysis->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Copy clusters
            $clusters = DB::table('anp_clusters')
                ->where('anp_network_structure_id', $analysis->anp_network_structure_id)
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
                ->where('anp_network_structure_id', $analysis->anp_network_structure_id)
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
                ->where('anp_network_structure_id', $analysis->anp_network_structure_id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($dependencies as $dep) {
                $newSourceId = null;
                $newTargetId = null;

                if ($dep->sourceable_type == 'App\\Models\\AnpCluster') {
                    $newSourceId = $clusterMapping[$dep->sourceable_id] ?? null;
                } elseif ($dep->sourceable_type == 'App\\Models\\AnpElement') {
                    $newSourceId = $elementMapping[$dep->sourceable_id] ?? null;
                }

                if ($dep->targetable_type == 'App\\Models\\AnpCluster') {
                    $newTargetId = $clusterMapping[$dep->targetable_id] ?? null;
                } elseif ($dep->targetable_type == 'App\\Models\\AnpElement') {
                    $newTargetId = $elementMapping[$dep->targetable_id] ?? null;
                }

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

            // Update analysis to use new structure
            DB::table('anp_analyses')
                ->where('id', $analysis->id)
                ->update([
                    'anp_network_structure_id' => $newStructureId,
                    'updated_at' => now()
                ]);

            Log::info("Isolated structure created", [
                'analysis_id' => $analysis->id,
                'old_structure_id' => $analysis->anp_network_structure_id,
                'new_structure_id' => $newStructureId,
                'clusters_copied' => count($clusterMapping),
                'elements_copied' => count($elementMapping)
            ]);
        }
    }

    public function down()
    {
        Schema::table('anp_network_structures', function (Blueprint $table) {
            $table->dropColumn(['original_analysis_id', 'is_template']);
        });
    }
}