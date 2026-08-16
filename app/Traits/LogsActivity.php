<?php

namespace App\Traits;

use App\Domain\ActivityLogs\Services\ActivityLogger;

trait LogsActivity
{
    /**
     * Boot the LogsActivity trait.
     */
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            app(ActivityLogger::class)->log($model, 'created', [
                'attributes' => $model->filterActivityAttributes(
                    $model->getAttributes()
                ),
            ]);
        });

        static::updated(function ($model) {
            $changes = $model->filterActivityAttributes($model->getChanges());

            // Do not log if only updated_at changed
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $old = array_intersect_key(
                $model->filterActivityAttributes($model->getOriginal()),
                $changes
            );

            app(ActivityLogger::class)->log($model, 'updated', [
                'old'     => $old,
                'changes' => $changes,
            ]);
        });

        static::deleted(function ($model) {
            app(ActivityLogger::class)->log($model, 'deleted', [
                'attributes' => $model->filterActivityAttributes(
                    $model->getAttributes()
                ),
            ]);
        });
    }

    /**
     * Optionally override hidden fields per model.
     * e.g. protected array $activityHidden = ['secret_field'];
     */
    protected function getActivityHiddenFields(): array
    {
        return array_merge(
            [
                'password',
                'remember_token',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'current_team_id',
                'email_verified_at',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            $this->activityHidden ?? []
        );
    }

    /**
     * Filter out sensitive/unwanted attributes from activity log.
     */
    public function filterActivityAttributes(array $attributes): array
    {
        foreach ($this->getActivityHiddenFields() as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }

    /**
     * Check if activity logging is enabled for this model.
     * Override in model: protected bool $logsActivity = false;
     */
    public function isLoggingActivity(): bool
    {
        return $this->logsActivity ?? true;
    }
}