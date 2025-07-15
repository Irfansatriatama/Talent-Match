<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnpElement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'anp_network_structure_id',  // Konsisten dengan kode lama
        'anp_cluster_id',            // Konsisten dengan kode lama
        'name',
        'description',
    ];

    protected $dates = ['deleted_at'];

    public function networkStructure(): BelongsTo
    {
        return $this->belongsTo(AnpNetworkStructure::class, 'anp_network_structure_id');
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(AnpCluster::class, 'anp_cluster_id');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(AnpDependency::class, 'element_id');
    }
}