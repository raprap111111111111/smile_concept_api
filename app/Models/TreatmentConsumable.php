<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of a treatment's recipe: "an extraction uses 2 anesthetic carpules".
 *
 * Audited, unlike the batch and movement models — changing a recipe silently
 * changes what every future procedure deducts, so it is worth a trail.
 */
class TreatmentConsumable extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'treatment_id',
        'item_id',
        'quantity_per_use',
        'is_optional',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_per_use' => 'integer',
            'is_optional'      => 'boolean',
        ];
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /** Lines automatic deduction acts on. Optional ones are suggestions only. */
    public function scopeDeductible(Builder $query): Builder
    {
        return $query->where('is_optional', false);
    }
}
