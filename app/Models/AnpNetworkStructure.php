<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnpNetworkStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_frozen',
        'parent_structure_id',
        'version',
        'frozen_at'
    ];

    protected $casts = [
        'is_frozen' => 'boolean',
        'frozen_at' => 'datetime',
    ];

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($networkStructure) {
            // Cek apakah struktur ini frozen
            if ($networkStructure->is_frozen) {
                throw new \Exception('Cannot delete frozen network structure');
            }
            
            // Soft delete semua relasi
            $networkStructure->clusters()->each(function($cluster) {
                $cluster->delete();
            });
            
            $networkStructure->elements()->each(function($element) {
                $element->delete();
            });
            
            $networkStructure->dependencies()->each(function($dependency) {
                $dependency->delete();
            });
        });
    }

    // Relasi ke parent structure
    public function parentStructure(): BelongsTo
    {
        return $this->belongsTo(AnpNetworkStructure::class, 'parent_structure_id');
    }

    // Relasi ke child structures
    public function childStructures(): HasMany
    {
        return $this->hasMany(AnpNetworkStructure::class, 'parent_structure_id');
    }

    // Method untuk freeze structure
    public function freeze()
    {
        $this->update([
            'is_frozen' => true,
            'frozen_at' => now()
        ]);
    }

    // Method untuk create snapshot
    public function createSnapshot($name = null)
    {
        $snapshot = $this->replicate();
        $snapshot->name = $name ?: $this->name . ' (Copy at ' . now()->format('Y-m-d H:i:s') . ')';
        $snapshot->parent_structure_id = $this->id;
        $snapshot->version = $this->version + 1;
        $snapshot->is_frozen = false;
        $snapshot->frozen_at = null;
        $snapshot->save();

        // Deep copy clusters
        $clusterMapping = [];
        foreach ($this->clusters as $cluster) {
            $newCluster = $snapshot->clusters()->create($cluster->only(['name', 'description']));
            $clusterMapping[$cluster->id] = $newCluster->id;
        }

        // Deep copy elements
        $elementMapping = [];
        foreach ($this->elements as $element) {
            $newElement = $snapshot->elements()->create([
                'name' => $element->name,
                'description' => $element->description,
                'anp_cluster_id' => $clusterMapping[$element->anp_cluster_id] ?? null
            ]);
            $elementMapping[$element->id] = $newElement->id;
        }

        // Deep copy dependencies
        foreach ($this->dependencies as $dependency) {
            $sourceId = null;
            $targetId = null;

            if ($dependency->sourceable_type == AnpCluster::class) {
                $sourceId = $clusterMapping[$dependency->sourceable_id] ?? null;
            } else {
                $sourceId = $elementMapping[$dependency->sourceable_id] ?? null;
            }

            if ($dependency->targetable_type == AnpCluster::class) {
                $targetId = $clusterMapping[$dependency->targetable_id] ?? null;
            } else {
                $targetId = $elementMapping[$dependency->targetable_id] ?? null;
            }

            if ($sourceId && $targetId) {
                $snapshot->dependencies()->create([
                    'sourceable_type' => $dependency->sourceable_type,
                    'sourceable_id' => $sourceId,
                    'targetable_type' => $dependency->targetable_type,
                    'targetable_id' => $targetId,
                    'description' => $dependency->description
                ]);
            }
        }

        return $snapshot;
    }

    // Existing relationships...
    public function clusters(): HasMany
    {
        return $this->hasMany(AnpCluster::class);
    }

    public function elements(): HasMany
    {
        return $this->hasMany(AnpElement::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(AnpDependency::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(AnpAnalysis::class);
    }
}