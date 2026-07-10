<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'price',
        'estimated_duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'estimated_duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function appointmentTreatments(): HasMany
    {
        return $this->hasMany(AppointmentTreatment::class);
    }
}
