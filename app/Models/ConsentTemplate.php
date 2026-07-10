<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsentTemplate extends Model
{
    use HasFactory,  LogsActivity;

    protected $fillable = [
        'title',
        'body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function patientConsents(): HasMany
    {
        return $this->hasMany(PatientConsent::class);
    }
}
