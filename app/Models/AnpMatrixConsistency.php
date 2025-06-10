<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AnpMatrixConsistency extends Model
{
    use HasFactory;

    protected $fillable = [
        'matrixable_id',
        'matrixable_type',
        'consistency_ratio',
        'is_consistent',
    ];

    protected $casts = [
        'is_consistent' => 'boolean',
        'consistency_ratio' => 'float',
    ];

    public function matrixable(): MorphTo
    {
        return $this->morphTo();
    }
}