<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnswer extends Model
{
    protected $primaryKey = 'user_answer_id';
    
    protected $fillable = [
        'user_id',
        'question_id',
        'selected_option_id',
        'riasec_score_selected',
        'answered_at'
    ];

    protected $casts = [
        'answered_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id', 'option_id');
    }
}