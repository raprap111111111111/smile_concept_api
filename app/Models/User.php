<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany, BelongsToMany, BelongsTo};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasRoles, HasFactory, HasApiTokens, Notifiable, LogsActivity;

    // ═══════════════════════════════════════════════════════
    // ✅ FILLABLE — All fields that can be mass-assigned
    // ═══════════════════════════════════════════════════════
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_photo',
        'branch_id',           // ✅ ADD
        'email_verified_at',   // ✅ ADD
    ];

    // ═══════════════════════════════════════════════════════
    // ✅ HIDDEN — Fields never exposed in JSON responses
    // ═══════════════════════════════════════════════════════
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ═══════════════════════════════════════════════════════
    // ✅ APPENDS — Auto-include these virtual attributes
    // ═══════════════════════════════════════════════════════
    protected $appends = [
        'profile_photo_url',   // ✅ Always available in JSON
    ];

    // ═══════════════════════════════════════════════════════
    // ✅ CASTS — Auto-convert types
    // ═══════════════════════════════════════════════════════
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ═══════════════════════════════════════════════════════
    // ✅ RELATIONSHIPS
    // ═══════════════════════════════════════════════════════

    /**
     * Branches the user works at (many-to-many)
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user')
                    ->withTimestamps();
    }

    /**
     * Primary branch (if branch_id is set)
     */
    public function primaryBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Doctor profile (if user is a doctor)
     */
    public function doctorProfile(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Patient profile (if user is a patient)
     */
    public function patientProfile(): HasOne
    {
        return $this->hasOne(PatientProfile::class);
    }

    /**
     * Appointments as a patient
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Appointments booked BY this user (as receptionist/admin)
     */
    public function bookedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'created_by');
    }

    /**
     * Social login accounts
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Payments recorded by this user (if receptionist)
     */
    public function recordedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'recorded_by');
    }

    // ═══════════════════════════════════════════════════════
    // ✅ ROLE HELPER METHODS
    // ═══════════════════════════════════════════════════════

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['admin', 'super-admin']);
    }

    public function isDoctor(): bool
    {
        return $this->hasRole('doctor');
    }

    public function isReceptionist(): bool
    {
        return $this->hasRole('receptionist');
    }

    public function isAssistant(): bool
    {
        return $this->hasRole(['assistant', 'staff']);
    }

    public function isPatient(): bool
    {
        return $this->hasRole('patient');
    }

    /**
     * Check if user has any staff role
     */
    public function isStaff(): bool
    {
        return $this->hasAnyRole([
            'super-admin',
            'admin',
            'doctor',
            'receptionist',
            'assistant',
            'staff',
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // ✅ SPATIE PERMISSION CONFIG
    // ═══════════════════════════════════════════════════════
    public function getDefaultGuardName(): string
    {
        return 'api';
    }

    // ═══════════════════════════════════════════════════════
    // ✅ ACCESSORS
    // ═══════════════════════════════════════════════════════

    /**
     * Full URL for profile photo (with fallback)
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->profile_photo
            ? asset('storage/' . $this->profile_photo)
            : asset('images/default-avatar.png');
    }

    /**
     * Get user's initials (for avatar placeholder)
     */
    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', trim($this->name));
        $initials = '';
        
        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper($part[0]);
                if (strlen($initials) >= 2) break;
            }
        }
        
        return $initials ?: '?';
    }

    /**
     * Check if user's email is verified
     */
    public function getIsVerifiedAttribute(): bool
    {
        return $this->email_verified_at !== null;
    }

    // ═══════════════════════════════════════════════════════
    // ✅ SCOPES (query helpers)
    // ═══════════════════════════════════════════════════════

    /**
     * Scope: Only verified users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope: Users in a specific branch
     */
    public function scopeInBranch($query, int $branchId)
    {
        return $query->whereHas('branches', function ($q) use ($branchId) {
            $q->where('branches.id', $branchId);
        });
    }

    /**
     * Scope: Search by name, email, or phone
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) return $query;
        
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}