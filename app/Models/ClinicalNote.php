<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalNote extends Model
{
    use HasFactory,  LogsActivity;

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'treatment_notes',
        'post_op_instructions',
        'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
