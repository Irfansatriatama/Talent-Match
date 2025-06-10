<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMbtiScore extends Model
{
    protected $primaryKey = 'user_mbti_score_id';
    
    protected $fillable = [
        'user_id',
        'ei_score_e', 'ei_score_i',
        'sn_score_s', 'sn_score_n',
        'tf_score_t', 'tf_score_f',
        'jp_score_j', 'jp_score_p',
        'ei_preference_strength',
        'sn_preference_strength',
        'tf_preference_strength',
        'jp_preference_strength',
        'mbti_type',
        'calculated_at'
    ];

    protected $casts = [
        'ei_preference_strength' => 'decimal:2',
        'sn_preference_strength' => 'decimal:2',
        'tf_preference_strength' => 'decimal:2',
        'jp_preference_strength' => 'decimal:2',
        'calculated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}