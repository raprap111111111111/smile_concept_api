<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToothCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'label',
        'color_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(DentalChartEntry::class, 'tooth_condition_id');
    }
}
