<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AnpAnalysis;
use App\Models\AnpNetworkStructure;
use App\Models\AnpCluster;
use App\Models\AnpElement;
use Illuminate\Support\Facades\DB;

class FixAnpNetworkIsolation extends Command
{
    protected $signature = 'anp:fix-isolation {--dry-run : Run without making changes}';
    protected $description = 'Fix ANP network structure isolation issues';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Scanning for shared network structures...');
        
        $sharedStructures = DB::table('anp_analyses')
            ->select('anp_network_structure_id', DB::raw('COUNT(*) as usage_count'))
            ->whereNotNull('anp_network_structure_id')
            ->groupBy('anp_network_structure_id')
            ->having('usage_count', '>', 1)
            ->get();
            
        if ($sharedStructures->isEmpty()) {
            $this->info('✓ No shared structures found. System is properly isolated.');
            return 0;
        }
        
        $this->warn('Found ' . $sharedStructures->count() . ' shared network structures');
        
        foreach ($sharedStructures as $shared) {
            $this->line("\nProcessing structure #{$shared->anp_network_structure_id} (used by {$shared->usage_count} analyses)");
            
            $analyses = AnpAnalysis::where('anp_network_structure_id', $shared->anp_network_structure_id)
                ->orderBy('id')
                ->get();
                
            $firstAnalysis = $analyses->shift();
            $this->line("  Keeping original for analysis #{$firstAnalysis->id}: {$firstAnalysis->name}");
            
            foreach ($analyses as $analysis) {
                $this->line("  Creating isolated copy for analysis #{$analysis->id}: {$analysis->name}");
                
                if (!$dryRun) {
                    $this->createIsolatedStructure($analysis);
                }
            }
        }
        
        if ($dryRun) {
            $this->info("\n[DRY RUN] No changes were made. Run without --dry-run to apply fixes.");
        } else {
            $this->info("\n✓ Successfully isolated all network structures.");
        }
        
        return 0;
    }
    
    private function createIsolatedStructure($analysis)
    {
        DB::transaction(function () use ($analysis) {
            $oldStructure = $analysis->networkStructure;
            
            $newStructure = AnpNetworkStructure::create([
                'name' => "Network untuk {$analysis->name} (Isolated)",
                'description' => $oldStructure->description . " (Isolated copy)",
                'is_frozen' => $oldStructure->is_frozen,
                'version' => 1,
                'original_analysis_id' => $analysis->id,
                'is_template' => false
            ]);
            
            $clusterMap = [];
            foreach ($oldStructure->clusters as $cluster) {
                $newCluster = $newStructure->clusters()->create([
                    'name' => $cluster->name,
                    'description' => $cluster->description
                ]);
                $clusterMap[$cluster->id] = $newCluster->id;
            }
            
            $elementMap = [];
            foreach ($oldStructure->elements as $element) {
                $newElement = $newStructure->elements()->create([
                    'anp_cluster_id' => $clusterMap[$element->anp_cluster_id] ?? null,
                    'name' => $element->name,
                    'description' => $element->description
                ]);
                $elementMap[$element->id] = $newElement->id;
            }
            
            foreach ($oldStructure->dependencies as $dep) {
                $sourceId = $dep->sourceable_type == AnpCluster::class ? 
                    ($clusterMap[$dep->sourceable_id] ?? null) : 
                    ($elementMap[$dep->sourceable_id] ?? null);
                    
                $targetId = $dep->targetable_type == AnpCluster::class ? 
                    ($clusterMap[$dep->targetable_id] ?? null) : 
                    ($elementMap[$dep->targetable_id] ?? null);
                    
                if ($sourceId && $targetId) {
                    $newStructure->dependencies()->create([
                        'sourceable_type' => $dep->sourceable_type,
                        'sourceable_id' => $sourceId,
                        'targetable_type' => $dep->targetable_type,
                        'targetable_id' => $targetId,
                        'description' => $dep->description
                    ]);
                }
            }
            
            $analysis->anp_network_structure_id = $newStructure->id;
            $analysis->save();
        });
    }
}