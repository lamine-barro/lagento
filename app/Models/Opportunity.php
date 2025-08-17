<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        // Identification
        'institution_id',
        'status',
        'title',
        'type',
        'description',
        'illustration_url',
        // Location
        'country',
        'target_regions',
        'city',
        'address',
        // Time
        'application_deadline',
        'start_date',
        'end_date',
        'duration',
        // Details
        'compensation',
        'seats',
        'eligibility_criteria',
        'required_documents',
        // Contact
        'contact_email',
        'contact_phone',
        'external_link',
    ];

    protected $casts = [
        'institution_id' => 'integer',
        'target_regions' => 'array',
        'application_deadline' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'seats' => 'integer',
        'eligibility_criteria' => 'array',
        'required_documents' => 'array',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    // Constants helpers
    public static function typeOptions(): array
    {
        return config('constants.TYPES_OPPORTUNITES', []);
    }

    public static function regionOptions(): array
    {
        return array_keys(config('constants.REGIONS', []));
    }
}


