<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        // Identity
        'project_name',
        'company_name',
        'description',
        'logo_url',
        // Formalisation
        'formalized',
        'incorporation_year',
        'rccm_number',
        // Activity
        'sectors',
        'products_services',
        'targets',
        // Development
        'maturity',
        'funding_stage',
        'revenue_models',
        'revenue_range',
        // Location
        'region',
        'latitude',
        'longitude',
        // Contact
        'phone',
        'email',
        'website',
        'social_links',
        // Team
        'num_founders_male',
        'num_founders_female',
        'founder_age_ranges',
        'founder_location',
        'team_size',
        // Needs
        'support_structures',
        'support_types',
        'needs_details',
        'newsletter_opt_in',
    ];

    protected $casts = [
        'formalized' => 'string',
        'incorporation_year' => 'integer',
        'sectors' => 'array',
        'products_services' => 'array',
        'targets' => 'array',
        'revenue_models' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'social_links' => 'array',
        'num_founders_male' => 'integer',
        'num_founders_female' => 'integer',
        'founder_age_ranges' => 'array',
        'support_structures' => 'array',
        'support_types' => 'array',
        'newsletter_opt_in' => 'boolean',
    ];

    // Constants helpers
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function sectorOptions(): array
    {
        return config('constants.SECTEURS', []);
    }

    public static function targetOptions(): array
    {
        return config('constants.CIBLES', []);
    }

    public static function maturityOptions(): array
    {
        return config('constants.STADES_MATURITE', []);
    }

    public static function fundingStageOptions(): array
    {
        return config('constants.STADES_FINANCEMENT', []);
    }

    public static function revenueModelOptions(): array
    {
        return config('constants.MODELES_REVENUS', []);
    }

    public static function revenueRangeOptions(): array
    {
        return config('constants.TRANCHES_REVENUS', []);
    }

    public static function regionOptions(): array
    {
        return array_keys(config('constants.REGIONS', []));
    }

    public static function founderAgeRangeOptions(): array
    {
        return config('constants.AGE_RANGES', []);
    }

    public static function teamSizeOptions(): array
    {
        return config('constants.TEAM_SIZES', []);
    }

    public static function supportStructureOptions(): array
    {
        return config('constants.STRUCTURES_ACCOMPAGNEMENT', []);
    }

    public static function supportTypeOptions(): array
    {
        return config('constants.TYPES_SOUTIEN', []);
    }
}


