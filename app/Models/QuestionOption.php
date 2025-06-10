<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $primaryKey = 'option_id';
    
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct_programming',
        'mbti_pole_represented',
        'display_order'
    ];

    protected $casts = [
        'is_correct_programming' => 'boolean'
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }
}