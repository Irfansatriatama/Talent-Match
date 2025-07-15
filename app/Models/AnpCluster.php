<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnpCluster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'anp_network_structure_id',
        'name',
        'description',
    ];

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($cluster) {
            // Soft delete semua elemen yang ada di dalam cluster ini
            $cluster->elements()->delete();

            // Soft delete juga semua dependensi yang menjadikan cluster ini sebagai sumber atau target
            \App\Models\AnpDependency::where(function ($query) use ($cluster) {
                $query->where('sourceable_type', self::class)
                      ->where('sourceable_id', $cluster->id);
            })->orWhere(function ($query) use ($cluster) {
                $query->where('targetable_type', self::class)
                      ->where('targetable_id', $cluster->id);
            })->delete();
        });
    }

    public function networkStructure(): BelongsTo
    {
        return $this->belongsTo(AnpNetworkStructure::class, 'anp_network_structure_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(AnpElement::class);
    }
}