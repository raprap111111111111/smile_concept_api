<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentTreatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'treatment_id',
        'tooth_number',
        'price_charged',
        'notes',
    ];

    protected $casts = [
        'price_charged' => 'decimal:2',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }
}