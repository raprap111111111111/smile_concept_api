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
     *
     * @param  Model  $subject  The model the action was performed on
     * @param  string $action   e.g. "created", "updated", "deleted", "logged_in"
     * @param  array  $properties Extra context (changed fields, old/new values, etc.)
     */
    public function log(Model $subject, string $action, array $properties = []): ActivityLog
    {
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
     */
    public function logEvent(string $action, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id'      => Auth::id(),
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