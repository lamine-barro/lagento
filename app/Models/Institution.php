<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'institutions';

    protected $fillable = [
        'type',
        'statut',
        'nom',
        'description',
        'services',
        'logo_url',
        'tags',
        'pays',
        'region',
        'ville',
        'adresse',
        'longitude',
        'latitude',
        'telephone',
        'email',
        'site_web',
    ];

    protected $casts = [
        'services' => 'array',
        'tags' => 'array',
        'longitude' => 'decimal:8',
        'latitude' => 'decimal:8',
    ];

    public function opportunites(): HasMany
    {
        return $this->hasMany(Opportunite::class);
    }

    public function textesOfficiels(): HasMany
    {
        return $this->hasMany(TexteOfficiel::class);
    }
}