<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnpStructureSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'anp_analysis_id',
        'anp_network_structure_id',
        'snapshot_data',
        'snapshot_type',
        'notes'
    ];

    protected $casts = [
        'snapshot_data' => 'array'
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(AnpAnalysis::class, 'anp_analysis_id');
    }

    public function networkStructure(): BelongsTo
    {
        return $this->belongsTo(AnpNetworkStructure::class, 'anp_network_structure_id');
    }

    // Helper method untuk save snapshot
    public static function createFromStructure(AnpAnalysis $analysis, AnpNetworkStructure $structure, $type = 'manual', $notes = null)
    {
        $snapshotData = [
            'clusters' => $structure->clusters->map(function($cluster) {
                return [
                    'id' => $cluster->id,
                    'name' => $cluster->name,
                    'description' => $cluster->description,
                    'elements' => $cluster->elements->map(function($element) {
                        return [
                            'id' => $element->id,
                            'name' => $element->name,
                            'description' => $element->description
                        ];
                    })
                ];
            }),
            'elements_without_cluster' => $structure->elements()->whereNull('anp_cluster_id')->get()->map(function($element) {
                return [
                    'id' => $element->id,
                    'name' => $element->name,
                    'description' => $element->description
                ];
            }),
            'dependencies' => $structure->dependencies->map(function($dep) {
                return [
                    'id' => $dep->id,
                    'source_type' => $dep->sourceable_type,
                    'source_id' => $dep->sourceable_id,
                    'source_name' => $dep->sourceable->name ?? 'N/A',
                    'target_type' => $dep->targetable_type,
                    'target_id' => $dep->targetable_id,
                    'target_name' => $dep->targetable->name ?? 'N/A',
                    'description' => $dep->description
                ];
            }),
            'timestamp' => now()->toIso8601String()
        ];

        return self::create([
            'anp_analysis_id' => $analysis->id,
            'anp_network_structure_id' => $structure->id,
            'snapshot_data' => $snapshotData,
            'snapshot_type' => $type,
            'notes' => $notes
        ]);
    }
}