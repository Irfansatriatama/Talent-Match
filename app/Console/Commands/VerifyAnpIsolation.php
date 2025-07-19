<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AnpAnalysis;
use App\Models\AnpNetworkStructure;
use Illuminate\Support\Facades\DB;

class VerifyAnpIsolation extends Command
{
    protected $signature = 'anp:verify-isolation';
    protected $description = 'Verify that each ANP analysis has isolated network structure';

    public function handle()
    {
        $this->info('Verifying ANP Network Isolation...');
        
        // Check for shared structures
        $sharedStructures = DB::table('anp_analyses')
            ->select('anp_network_structure_id', DB::raw('COUNT(*) as usage_count'))
            ->whereNotNull('anp_network_structure_id')
            ->groupBy('anp_network_structure_id')
            ->having('usage_count', '>', 1)
            ->get();
            
        if ($sharedStructures->count() > 0) {
            $this->error('Found shared network structures:');
            foreach ($sharedStructures as $shared) {
                $this->warn("Structure #{$shared->anp_network_structure_id} is used by {$shared->usage_count} analyses");
                
                $analyses = AnpAnalysis::where('anp_network_structure_id', $shared->anp_network_structure_id)
                    ->get(['id', 'name']);
                    
                foreach ($analyses as $analysis) {
                    $this->line("  - Analysis #{$analysis->id}: {$analysis->name}");
                }
            }
            return 1;
        }
        
        $this->info('✓ All analyses have isolated network structures');
        
        // Show statistics
        $totalAnalyses = AnpAnalysis::count();
        $totalStructures = AnpNetworkStructure::count();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Analyses', $totalAnalyses],
                ['Total Network Structures', $totalStructures],
                ['Isolated Structures', AnpNetworkStructure::whereNotNull('original_analysis_id')->count()],
                ['Template Structures', AnpNetworkStructure::where('is_template', true)->count()]
            ]
        );
        
        return 0;
    }
}