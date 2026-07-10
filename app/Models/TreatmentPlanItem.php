<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'treatment_plan_id',
        'treatment_id',
        'sequence_order',
        'estimated_cost',
        'notes',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'estimated_cost' => 'decimal:2',
    ];

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }
}
