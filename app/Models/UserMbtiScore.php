<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMbtiScore extends Model
{
    protected $primaryKey = 'user_mbti_score_id';
    
    /**
     *
     *
     * @var array
     */
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
        'ei_score_e' => 'integer',
        'ei_score_i' => 'integer',
        'sn_score_s' => 'integer',
        'sn_score_n' => 'integer',
        'tf_score_t' => 'integer',
        'tf_score_f' => 'integer',
        'jp_score_j' => 'integer',
        'jp_score_p' => 'integer',
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

    /**
     *
     * @return array
     */
    public function getDominantFunctionsAttribute(): array
    {
        return [
            'EI' => $this->ei_score_e > $this->ei_score_i ? 'E' : 'I',
            'SN' => $this->sn_score_s > $this->sn_score_n ? 'S' : 'N',
            'TF' => $this->tf_score_t > $this->tf_score_f ? 'T' : 'F',
            'JP' => $this->jp_score_j > $this->jp_score_p ? 'J' : 'P'
        ];
    }

    /**
     *
     * @return array
     */
    public function getDetailedScoresAttribute(): array
    {
        return [
            'EI' => ['E' => $this->ei_score_e, 'I' => $this->ei_score_i],
            'SN' => ['S' => $this->sn_score_s, 'N' => $this->sn_score_n],
            'TF' => ['T' => $this->tf_score_t, 'F' => $this->tf_score_f],
            'JP' => ['J' => $this->jp_score_j, 'P' => $this->jp_score_p]
        ];
    }
}