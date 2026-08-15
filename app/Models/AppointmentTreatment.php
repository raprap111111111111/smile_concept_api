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
        // How many times this procedure was performed. Read by stock deduction
        // only — price_charged is unaffected and still means what it always did.
        'quantity',
        'price_charged',
        'notes',
    ];

    protected $casts = [
        'price_charged' => 'decimal:2',
        'quantity' => 'integer',
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