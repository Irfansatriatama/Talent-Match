<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnpAnalysis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'job_position_id',
        'anp_network_structure_id',
        'hr_user_id',
        'status',
        'calculation_data',
        'description',
    ];

    protected $dates = ['deleted_at', 'completed_at']; 

    protected $casts = [
        'calculation_data' => 'array'
    ];

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function networkStructure(): BelongsTo
    {
        return $this->belongsTo(AnpNetworkStructure::class, 'anp_network_structure_id');
    }

    public function hasNetworkStructure(): bool
    {
        return !is_null($this->anp_network_structure_id);
    }

    public function isNetworkPending(): bool
    {
        return $this->status === 'network_pending' && !$this->hasNetworkStructure();
    }

    public function hrUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_user_id');
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'anp_analysis_candidates', 'anp_analysis_id', 'user_id')
                    ->withTimestamps();
    }

    public function criteriaComparisons(): HasMany
    {
        return $this->hasMany(AnpCriteriaComparison::class);
    }

    public function interdependencyComparisons(): HasMany
    {
        return $this->hasMany(AnpInterdependencyComparison::class);
    }

    public function alternativeComparisons(): HasMany
    {
        return $this->hasMany(AnpAlternativeComparison::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(AnpResult::class);
    }

    public function ensureUniqueNetworkStructure()
    {
        if (!$this->anp_network_structure_id) {
            return false;
        }
    
        // Check if structure is truly unique
        $sharedCount = self::where('anp_network_structure_id', $this->anp_network_structure_id)
            ->where('id', '!=', $this->id)
            ->count();
            
        return $sharedCount === 0;
    }

    public function validateNetworkStructure()
    {
        if (!$this->anp_network_structure_id) {
            throw new \Exception('Analysis does not have network structure');
        }
        
        if (!$this->ensureUniqueNetworkStructure()) {
            throw new \Exception('Analysis is using shared network structure');
        }
        
        return true;
    }

    /**
     * 
     * 
     *
     * @return boolean
     */
    public function isReadyForCalculation(): bool
    {
        $this->loadMissing(['networkStructure.elements', 'networkStructure.clusters', 'networkStructure.dependencies']);
        
        
        $criteriaCompsValid = $this->criteriaComparisons()
            ->whereHas('consistency', fn($q) => $q->where('is_consistent', true))
            ->exists();
            
        if (!$criteriaCompsValid) {
            \Log::info("ANP Analysis {$this->id}: No valid criteria comparisons found");
            return false;
        }
        
        
        $dependencyCount = $this->networkStructure->dependencies()->count();
        $validInterdepCount = $this->interdependencyComparisons()
            ->whereHas('consistency', fn($q) => $q->where('is_consistent', true))
            ->count();
            
        if ($dependencyCount > 0 && $validInterdepCount < $dependencyCount) {
            \Log::info("ANP Analysis {$this->id}: Interdependency comparisons incomplete. Required: {$dependencyCount}, Valid: {$validInterdepCount}");
            return false;
        }
        
        
        $elementCount = $this->networkStructure->elements()->count();
        $validAltCount = $this->alternativeComparisons()
            ->whereHas('consistency', fn($q) => $q->where('is_consistent', true))
            ->count();
            
        if ($validAltCount < $elementCount) {
            \Log::info("ANP Analysis {$this->id}: Alternative comparisons incomplete. Required: {$elementCount}, Valid: {$validAltCount}");
            return false;
        }
        
        \Log::info("ANP Analysis {$this->id}: All comparisons are valid and ready for calculation");
        return true;
    }
}