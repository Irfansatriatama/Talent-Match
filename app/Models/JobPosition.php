<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosition extends Model
{
    use HasFactory, SoftDeletes;

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

    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function analyses()
    {
        return $this->hasMany(AnpAnalysis::class, 'job_position_id');
    }
}