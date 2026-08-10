<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'consent_template_id',
        'user_id',
        'appointment_id',
        'signed_by_staff_id',
        'signed_at',
        'signature_data',
        'ip_address',
        'user_agent',
        'voided_at',
        'voided_reason',
        'voided_by',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(ConsentTemplate::class, 'consent_template_id');
    }

    /**
     * The PATIENT this consent is FOR.
     * user_id must always be a patient — never staff/superadmin.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Staff who witnessed / facilitated the signing.
     * Null when the patient signed themselves.
     */
    public function signedByStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_staff_id');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    // ─── State helpers ────────────────────────────────────────────────────────

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    public function isValid(): bool
    {
        return $this->voided_at === null;
    }
}