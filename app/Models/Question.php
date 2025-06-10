<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $primaryKey = 'question_id';
    
    protected $fillable = [
        'test_id',
        'question_text',
        'question_order',
        'programming_category',
        'riasec_dimension',
        'mbti_dichotomy',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id', 'test_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id', 'question_id')
                    ->orderBy('display_order');
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'question_id', 'question_id');
    }
}