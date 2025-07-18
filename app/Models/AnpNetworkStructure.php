<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnpNetworkStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($networkStructure) {
            // Soft delete semua cluster
            $networkStructure->clusters()->delete();
            
            // Soft delete semua element
            $networkStructure->elements()->delete();
            
            // Soft delete semua dependency
            $networkStructure->dependencies()->delete();
        });
    }

    public function clusters(): HasMany
    {
        return $this->hasMany(AnpCluster::class);
    }

    public function elements(): HasMany
    {
        return $this->hasMany(AnpElement::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(AnpDependency::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(AnpAnalysis::class);
    }
}