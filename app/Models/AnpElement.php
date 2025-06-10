<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnpElement extends Model
{
    use HasFactory;

    protected $fillable = [
        'anp_network_structure_id',
        'anp_cluster_id',
        'name',
        'description',
    ];

    public function networkStructure(): BelongsTo
    {
        return $this->belongsTo(AnpNetworkStructure::class, 'anp_network_structure_id');
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(AnpCluster::class, 'anp_cluster_id');
    }
}