<?php

namespace App\Models;

use App\Enums\TreatmentPlanStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPlan extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'name',
        'status',
        'total_estimated_amount',
        'notes',
    ];

    protected $casts = [
        'status' => TreatmentPlanStatus::class,
        'total_estimated_amount' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TreatmentPlanItem::class)->orderBy('sequence_order', 'asc');
    }
}
