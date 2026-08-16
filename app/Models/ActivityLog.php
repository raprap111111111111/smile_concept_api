<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
        'url',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ Prevent logs from ever being modified or deleted
    protected static function boot(): void
    {
        parent::boot();

        static::updating(fn() => false);
        static::deleting(fn() => false);
    }

    // ================= Relationships =================

    /**
     * User who performed the action.
     * Auto-detects SoftDeletes to safely include trashed users.
     */
    public function user(): BelongsTo
    {
        $relation = $this->belongsTo(User::class);

        // ✅ Only add withTrashed() if User actually uses SoftDeletes
        if (in_array(SoftDeletes::class, class_uses_recursive(User::class))) {
            $relation->withTrashed();
        }

        return $relation;
    }

    /**
     * The model the action was performed on.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // ================= Scopes =================

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeForSubject($query, string $type, int $id)
    {
        return $query->where('subject_type', $type)
                     ->where('subject_id', $id);
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}