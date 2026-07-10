<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabCase extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'appointment_id',
        'lab_name',
        'work_type',
        'status',
        'sent_date',
        'due_date',
        'received_date',
        'cost',
        'notes',
    ];

    protected $casts = [
        'sent_date' => 'date',
        'due_date' => 'date',
        'received_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
