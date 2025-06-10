<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'ideal_riasec_profile', 
        'ideal_mbti_profile',   
    ];

    protected $casts = [
        'ideal_riasec_profile' => 'array', 
        'ideal_mbti_profile' => 'array',   
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}