<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;


class AnpCriteriaComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'anp_analysis_id',
        'control_criterionable_id',  
        'control_criterionable_type',
        'compared_elements_type',   
        'comparison_data',         
        'priority_vector',         
    ];

    protected $casts = [
        'comparison_data' => 'array',
        'priority_vector' => 'array',
    ];

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($model) {
            $model->consistency()->delete();
        });
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(AnpAnalysis::class, 'anp_analysis_id');
    }

    public function controlCriterionable(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function consistency(): MorphOne
    {
        return $this->morphOne(AnpMatrixConsistency::class, 'matrixable');
    }
}