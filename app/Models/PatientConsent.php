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
        'signed_at',
        'signature_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ConsentTemplate::class, 'consent_template_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
