<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalChartEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'dental_chart_id',
        'tooth_number',
        'tooth_condition_id',
        'treatment_applied',
    ];

    public function chart(): BelongsTo
    {
        return $this->belongsTo(DentalChart::class, 'dental_chart_id');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(ToothCondition::class, 'tooth_condition_id');
    }
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }
}
