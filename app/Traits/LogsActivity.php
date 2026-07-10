<?php

namespace App\Traits;

use App\Domain\ActivityLogs\Services\ActivityLogger;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            app(ActivityLogger::class)->log($model, 'created', [
                'attributes' => self::filterActivityAttributes($model->getAttributes()),
            ]);
        });

        static::updated(function ($model) {
            $changes = self::filterActivityAttributes($model->getChanges());

            // Do not log if only updated_at changed
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $old = array_intersect_key($model->getOriginal(), $changes);

            app(ActivityLogger::class)->log($model, 'updated', [
                'old'     => self::filterActivityAttributes($old),
                'changes' => $changes,
            ]);
        });

        static::deleted(function ($model) {
            app(ActivityLogger::class)->log($model, 'deleted', [
                'attributes' => self::filterActivityAttributes($model->getAttributes()),
            ]);
        });
    }

    protected static function filterActivityAttributes(array $attributes): array
    {
        $hidden = [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'current_team_id',
            'email_verified_at',
            'created_at',
            'updated_at',
        ];

        foreach ($hidden as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }
}