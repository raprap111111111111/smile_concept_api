<?php

namespace App\Domain\ActivityLogs\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Record an activity for any model.
     */
    public function log(
        Model $subject,
        string $action,
        array $properties = []
    ): ActivityLog {
        return ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'subject_type' => $subject::class,
            'subject_id'   => $subject->getKey(),
            'properties'   => $properties,
            'ip_address'   => Request::ip(),
            'user_agent'   => Request::userAgent(),
            'url'          => Request::fullUrl(),
        ]);
    }

    /**
     * Log a non-model action (e.g. login, logout, export).
     * 
     * @param int|null $userId  Override Auth::id() when user isn't in session yet
     */
    public function logEvent(
        string $action,
        array $properties = [],
        ?int $userId = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id'      => $userId ?? Auth::id(),
            'action'       => $action,
            'subject_type' => 'system',
            'subject_id'   => 0,
            'properties'   => $properties,
            'ip_address'   => Request::ip(),
            'user_agent'   => Request::userAgent(),
            'url'          => Request::fullUrl(),
        ]);
    }
}