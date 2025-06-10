<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class AnpInterdependencyComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'anp_analysis_id',
        'anp_dependency_id',
        'comparison_data',  
        'priority_vector',  
    ];

    protected $casts = [
        'comparison_data' => 'array',
        'priority_vector' => 'array',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(AnpAnalysis::class, 'anp_analysis_id');
    }

    public function dependency(): BelongsTo
    {
        return $this->belongsTo(AnpDependency::class, 'anp_dependency_id');
    }
    
    public function consistency(): MorphOne
    {
        return $this->morphOne(AnpMatrixConsistency::class, 'matrixable');
    }
}