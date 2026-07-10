<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'unit_of_measure',
        'minimum_threshold',
    ];

    protected $casts = [
        'minimum_threshold' => 'integer',
    ];
}
