<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    public const ROLE_CANDIDATE = 'candidate';
    public const ROLE_HR = 'HR';


    public const JOB_POSITION_SOFTWARE_ENGINEER = 'Software Engineer';
    public const JOB_POSITION_DATA_ANALYST = 'Data Analyst';
    public const JOB_POSITION_CYBERSECURITY_SPECIALIST = 'Cybersecurity Specialist';

    public static array $allowedJobPositions = [
        self::JOB_POSITION_SOFTWARE_ENGINEER,
        self::JOB_POSITION_DATA_ANALYST,
        self::JOB_POSITION_CYBERSECURITY_SPECIALIST,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'job_position_id',
        'profile_summary',
    ];

    /**
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class);
    }
    public function testProgress(): HasMany
    {
        return $this->hasMany(UserTestProgress::class, 'user_id', 'id');
    }

    public function mbtiScores(): HasMany
    {
        return $this->hasMany(UserMbtiScore::class, 'user_id', 'id')->orderBy('calculated_at', 'desc');
    }

    public function latestMbtiScore(): HasOne
    {
        return $this->hasOne(UserMbtiScore::class, 'user_id', 'id')->latestOfMany('calculated_at');
    }

    public function riasecScore(): HasOne
    {
        return $this->hasOne(UserRiasecScore::class, 'user_id', 'id');
    }

    public function latestRiasecScore(): HasOne
    {
        return $this->hasOne(UserRiasecScore::class, 'user_id', 'id')->latestOfMany('calculated_at');
    }

    public function testSessions(): HasMany
    {
        return $this->hasMany(TestSession::class, 'user_id', 'id');
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'user_id', 'id');
    }

    public function anpCalculationsMade(): HasMany
    {
        return $this->hasMany(ANPCalculation::class, 'calculated_by', 'id');
    }

    public function isCandidate(): bool
    {
        return $this->role === self::ROLE_CANDIDATE;
    }

    public function isHr(): bool
    {
        return $this->role === self::ROLE_HR;
    }

    public function hasCompletedAllTests(int $requiredTestCount = 3): bool
    {
        $completedProgram = $this->testProgress()
            ->where('test_id', 1)
            ->where('status', 'completed')
            ->exists();

        $completedRiasec = $this->riasecScore()->exists();
        $completedMbti = $this->latestMbtiScore()->exists();

        $completedCount = ($completedProgram ? 1 : 0) +
                          ($completedRiasec ? 1 : 0) +
                          ($completedMbti ? 1 : 0);

        return $completedCount >= $requiredTestCount;
    }

    
    public function getTestCompletionPercentage(int $totalTests = 3): float
    {
        if ($totalTests === 0) {
            return 0.0;
        }

        $completedCount = 0;

        // Cek programming test
        if ($this->testProgress()->where('test_id', 1)->where('status', 'completed')->exists()) {
            $completedCount++;
        }

        // Cek RIASEC test
        if ($this->riasecScore()->exists()) {
            $completedCount++;
        }

        // Cek MBTI test
        if ($this->latestMbtiScore()->exists()) {
            $completedCount++;
        }

        return ($completedCount / $totalTests) * 100;
    }
}