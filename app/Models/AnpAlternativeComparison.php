<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class AnpAlternativeComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'anp_analysis_id',
        'anp_element_id',   
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

    public function element(): BelongsTo
    {
        return $this->belongsTo(AnpElement::class, 'anp_element_id');
    }
    
    public function consistency(): MorphOne
    {
        return $this->morphOne(AnpMatrixConsistency::class, 'matrixable');
    }
}