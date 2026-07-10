<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'branch_id',
        'item_id',
        'quantity',
        'expiry_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expiry_date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Helper to verify if the physical quantity has fallen below the safety threshold
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= ($this->item?->minimum_threshold ?? 10);
    }

    /**
     * Helper to verify if the stock batch has expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return $this->expiry_date->isPast();
    }
}
