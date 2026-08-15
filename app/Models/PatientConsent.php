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
        'signed_by_guardian_id',   // ✅ NEW — guardian who signed for minor/dependent
        'signer_relationship',     // ✅ NEW — 'self' | 'guardian' | 'staff'
        'signed_at',
        'signature_data',
        'form_data',               // ✅ Stores checkboxes, radios, initials as JSON
        'ip_address',
        'user_agent',
        'voided_at',
        'voided_reason',
        'voided_by',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'voided_at' => 'datetime',
        'form_data' => 'array',    // ✅ auto JSON encode/decode
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
     * Null when the patient signed themselves OR guardian signed.
     */
    public function signedByStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_staff_id');
    }

    /**
     * ✅ NEW — Guardian who signed on behalf of a minor/dependent patient.
     * Null when patient signed themselves OR staff facilitated.
     */
    public function signedByGuardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_guardian_id');
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

    // ─── Signer role helpers ──────────────────────────────────────────────────

    /**
     * ✅ Was this signed by the patient themselves?
     */
    public function isSelfSigned(): bool
    {
        return $this->signer_relationship === 'self'
            || ($this->signed_by_guardian_id === null && $this->signed_by_staff_id === null);
    }

    /**
     * ✅ Was this signed by a parent/guardian?
     */
    public function isGuardianSigned(): bool
    {
        return $this->signer_relationship === 'guardian'
            || $this->signed_by_guardian_id !== null;
    }

    /**
     * ✅ Was this signed with staff assistance?
     */
    public function isStaffWitnessed(): bool
    {
        return $this->signer_relationship === 'staff'
            || $this->signed_by_staff_id !== null;
    }

    /**
     * ✅ Human-readable signer description for UI/PDF.
     */
    public function getSignerDescriptionAttribute(): string
    {
        return match (true) {
            $this->isGuardianSigned() => "Signed by guardian: " . ($this->signedByGuardian?->name ?? 'Unknown'),
            $this->isStaffWitnessed() => "Witnessed by staff: " . ($this->signedByStaff?->name ?? 'Unknown'),
            default                    => "Self-signed by patient",
        };
    }

    // ─── Form data helpers ────────────────────────────────────────────────────

    /**
     * ✅ Get a specific clause acknowledgement.
     * Example: $consent->getClauseData('treatment_to_be_done')
     * Returns: ['agreed' => true, 'initial' => 'JD']
     */
    public function getClauseData(string $clauseKey): array
    {
        return $this->form_data['clauses'][$clauseKey] ?? [
            'agreed'  => false,
            'initial' => '',
        ];
    }

    /**
     * ✅ Check if a specific clause was acknowledged.
     */
    public function isClauseAgreed(string $clauseKey): bool
    {
        return $this->getClauseData($clauseKey)['agreed'] ?? false;
    }

    /**
     * ✅ Get initials for a specific clause.
     */
    public function getClauseInitial(string $clauseKey): string
    {
        return $this->getClauseData($clauseKey)['initial'] ?? '';
    }

    /**
     * ✅ Get medical answer (yes/no or text).
     * Example: $consent->getMedicalAnswer('in_good_health')
     */
    public function getMedicalAnswer(string $key, mixed $default = null): mixed
    {
        return $this->form_data['medical'][$key] ?? $default;
    }

    /**
     * ✅ Check if a specific medical condition was selected.
     * Example: $consent->hasCondition('diabetes')
     */
    public function hasCondition(string $condition): bool
    {
        $conditions = $this->form_data['medical']['conditions'] ?? [];
        return in_array($condition, (array) $conditions, true);
    }

    /**
     * ✅ Check if a specific allergy was selected.
     */
    public function hasAllergy(string $allergy): bool
    {
        $allergies = $this->form_data['medical']['allergy_types'] ?? [];
        return in_array($allergy, (array) $allergies, true);
    }

    /**
     * ✅ Get all selected conditions.
     */
    public function getSelectedConditions(): array
    {
        return $this->form_data['medical']['conditions'] ?? [];
    }

    /**
     * ✅ Get all selected allergies.
     */
    public function getSelectedAllergies(): array
    {
        return $this->form_data['medical']['allergy_types'] ?? [];
    }
}