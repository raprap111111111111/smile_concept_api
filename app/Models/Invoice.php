<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'appointment_id',
        'total_amount',
        'balance_due',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'status' => InvoiceStatus::class,
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
