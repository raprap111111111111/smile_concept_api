<?php
// app/Models/Doctor.php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use SoftDeletes,  LogsActivity; // ✅ ADD

    protected $fillable = [
        'user_id',
        'license_number',
        'specialization',
        'bio',                    // ✅ ADD
        'consultation_fee',       // ✅ ADD
        'years_of_experience',    // ✅ ADD
        'signature_path',         // ✅ ADD
        'is_active',              // ✅ ADD
    ];

    // ✅ ADD casts for proper types
    protected $casts = [
        'consultation_fee'   => 'decimal:2',
        'years_of_experience' => 'integer',
        'is_active'          => 'boolean',
    ];

    // ✅ ADD default values
    protected $attributes = [
        'is_active'           => true,
        'years_of_experience' => 0,
    ];

    // ─── Relationships ─────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    // ✅ ADD accessor for doctor's name (via user)
    public function getNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown Doctor';
    }

    // ✅ ADD scope for active doctors
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}