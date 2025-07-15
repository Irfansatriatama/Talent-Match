<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AnpDependency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'anp_network_structure_id',
        'sourceable_id',
        'sourceable_type',
        'targetable_id',
        'targetable_type',
        'description',
    ];

    protected $dates = ['deleted_at'];

    public function networkStructure(): BelongsTo
    {
        return $this->belongsTo(AnpNetworkStructure::class, 'anp_network_structure_id');
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function targetable(): MorphTo
    {
        return $this->morphTo();
    }
}