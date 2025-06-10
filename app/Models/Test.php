<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    protected $primaryKey = 'test_id';
    
    protected $fillable = [
        'test_name',
        'test_type',
        'description',
        'test_order',
        'time_limit_minutes'
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'test_id', 'test_id')
                    ->where('is_active', true)
                    ->orderBy('question_order');
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserTestProgress::class, 'test_id', 'test_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TestSession::class, 'test_id', 'test_id');
    }

    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }
}