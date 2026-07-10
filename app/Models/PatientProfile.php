<?php

namespace App\Models;

use App\Enums\BloodType;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientProfile extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        // Demographics
        'date_of_birth', 'gender', 'civil_status', 'nationality', 'occupation',
        // Address
        'address', 'city', 'province', 'postal_code',
        // Medical
        'blood_type', 'allergies', 'medical_history',
        'current_medications', 'dental_history',
        // Medical alerts
        'requires_epinephrine_free_anesthesia',
        'has_cardiac_conditions',
        'is_pregnant',
        'has_bleeding_disorders',
        // Emergency
        'emergency_contact_name', 'emergency_contact_phone',
        // Insurance
        'insurance_provider', 'insurance_number',
        // Referral
        'referred_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'blood_type'    => BloodType::class,
        'requires_epinephrine_free_anesthesia' => 'boolean',
        'has_cardiac_conditions'               => 'boolean',
        'is_pregnant'                          => 'boolean',
        'has_bleeding_disorders'               => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}