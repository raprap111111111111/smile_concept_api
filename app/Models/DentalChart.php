<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DentalChart extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'appointment_id',
        'general_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DentalChartEntry::class, 'dental_chart_id');
    }
}
