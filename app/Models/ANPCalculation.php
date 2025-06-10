<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ANPCalculation extends Model
{
    protected $fillable = [
        'calculated_by',
        'calculation_date',
        'job_position',
        'input_data',
        'anp_weights',
        'final_rankings',
        'notes'
    ];
    
    protected $casts = [
        'calculation_date' => 'datetime',
        'input_data' => 'array',
        'anp_weights' => 'array',
        'final_rankings' => 'array'
    ];
    
    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}