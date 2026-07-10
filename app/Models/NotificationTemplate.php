<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class NotificationTemplate extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'key',
        'name',
        'subject',
        'body',
        'channels',
        'variables',
        'trigger_event',
        'is_active',
    ];

    protected $casts = [
        'channels' => 'array',
        'variables' => 'array',
        'is_active' => 'boolean',
    ];
}