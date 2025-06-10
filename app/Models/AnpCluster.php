<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnpCluster extends Model
{
    use HasFactory;

    protected $fillable = [
        'anp_network_structure_id',
        'name',
        'description',
    ];

    public function networkStructure(): BelongsTo
    {
        return $this->belongsTo(AnpNetworkStructure::class, 'anp_network_structure_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(AnpElement::class);
    }
}