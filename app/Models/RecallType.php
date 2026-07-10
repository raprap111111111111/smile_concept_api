<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecallType extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'label',
        'frequency_months',
        'is_active',
    ];

    protected $casts = [
        'frequency_months' => 'integer',
        'is_active' => 'boolean',
    ];

    public function recalls(): HasMany
    {
        return $this->hasMany(Recall::class, 'recall_type_id');
    }
}
