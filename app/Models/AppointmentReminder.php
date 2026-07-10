<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'channel',
        'status',
        'scheduled_for',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at'       => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    // ─── Helper Scopes ─────────────────────────────────
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'pending')
                     ->where('scheduled_for', '<=', now());
    }

    // ─── Status Helpers ────────────────────────────────
    public function markAsSent(): void
    {
        $this->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status'        => 'failed',
            'error_message' => $reason,
        ]);
    }
}