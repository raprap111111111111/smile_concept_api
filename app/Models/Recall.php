<?php

namespace App\Models;

use App\Enums\RecallStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recall extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recall_type_id',
        'due_date',
        'status',
        'last_notified_at',
    ];

    protected $casts = [
        'status' => RecallStatus::class,
        'due_date' => 'date',
        'last_notified_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recallType(): BelongsTo
    {
        return $this->belongsTo(RecallType::class, 'recall_type_id');
    }
}
