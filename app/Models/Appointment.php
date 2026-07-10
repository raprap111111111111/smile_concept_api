<?php
// app/Models/Appointment.php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int $id
 * @property \App\Enums\AppointmentStatus $status
 * @property \App\Models\User $user
 * @property \App\Models\Doctor $doctor
 * @property \App\Models\Branch $branch
 * @property \App\Models\User $creator
 * @property \App\Models\Invoice|null $invoice
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\AppointmentTreatment[] $treatments
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\AppointmentReminder[] $reminders
 */
class Appointment extends Model
{
    use HasFactory, SoftDeletes,  LogsActivity;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'branch_id',
        'start_time',
        'end_time',
        'status',
        'reason_for_visit',     // ✅ ADD
        'cancellation_reason',  // ✅ ADD
        'created_by',           // ✅ ADD
        'reminder_sent',
    ];

    protected $casts = [
        'start_time'    => 'datetime',
        'end_time'      => 'datetime',
        'reminder_sent' => 'boolean',
        'status'        => AppointmentStatus::class,
    ];

    // ═══════════════════════════════════════════════════════
    // ✅ RELATIONSHIPS
    // ═══════════════════════════════════════════════════════

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // ✅ ADD — Who created this appointment
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ✅ ADD — Invoice link
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    // ✅ ADD — Treatments performed
    public function treatments(): HasMany
    {
        return $this->hasMany(AppointmentTreatment::class);
    }

    // ✅ ADD — Reminders history
    public function reminders(): HasMany
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    // ═══════════════════════════════════════════════════════
    // ✅ HELPERS
    // ═══════════════════════════════════════════════════════

    public function isOverlapping(string $startTime, string $endTime): bool
    {
        return $this->start_time < $endTime && $this->end_time > $startTime;
    }

    public function isInThePast(): bool
    {
        return $this->end_time < now();
    }

    public function isCompleted(): bool
    {
        return $this->status === AppointmentStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === AppointmentStatus::CANCELLED;
    }

    public function hasInvoice(): bool
    {
        return $this->invoice()->exists();
    }

    // ═══════════════════════════════════════════════════════
    // ✅ SCOPES
    // ═══════════════════════════════════════════════════════

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    public function scopeByStatus($query, string|AppointmentStatus $status)
    {
        return $query->where('status', $status instanceof AppointmentStatus ? $status->value : $status);
    }
}