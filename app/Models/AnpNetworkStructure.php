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