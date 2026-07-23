<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAttachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'appointment_id',
        'file_name',
        'file_path',
        'file_type',
        'category',
        'is_xray',
        'scan_status',
        'scan_results',
        'detected_conditions',
        'scan_confidence',
        'scanned_at',
        'scan_provider',
        'notes',
    ];

    protected $casts = [
        'is_xray'             => 'boolean',
        'scan_confidence'     => 'float',
        'detected_conditions' => 'array',
        'scan_results'        => 'array',
        'scanned_at'          => 'datetime',
        'appointment_id'      => 'integer',
        'user_id'             => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────

    /**
     * The patient who OWNS this attachment.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}