<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnpResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'anp_analysis_id',
        'user_id', 
        'score',
        'rank',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(AnpAnalysis::class, 'anp_analysis_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}