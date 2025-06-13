<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRiasecScore extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_riasec_scores';
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_riasec_score_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'r_score', 
        'i_score',   
        'a_score',  
        's_score',    
        'e_score',   
        'c_score',    
        'riasec_code',
        'calculated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'r_score' => 'integer',
        'i_score' => 'integer',
        'a_score' => 'integer',
        's_score' => 'integer',
        'e_score' => 'integer',
        'c_score' => 'integer',
        'calculated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 
     *
     * @return string|null
     */
    public function getDominantTypeAttribute(): ?string
    {
        return $this->riasec_code ? substr($this->riasec_code, 0, 1) : null;
    }

    /**
     * 
     *
     * @return array
     */
    public function getScoresArrayAttribute(): array
    {
        return [
            'R' => $this->r_score,
            'I' => $this->i_score,
            'A' => $this->a_score,
            'S' => $this->s_score,
            'E' => $this->e_score,
            'C' => $this->c_score
        ];
    }
}