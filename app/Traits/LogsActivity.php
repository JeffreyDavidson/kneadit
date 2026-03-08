<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(fn ($model) => static::logAction($model, 'created'));
        static::updated(fn ($model) => static::logAction($model, 'updated', $model->getChanges()));
        static::deleted(fn ($model) => static::logAction($model, 'deleted'));
    }

    protected static function logAction($model, string $action, array $changes = []): void
    {
        // Prevent recursive logging
        if ($model instanceof ActivityLog) {
            return;
        }

        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name ?? 'System',
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'description' => class_basename($model) . " #{$model->getKey()} was {$action}",
                'properties' => !empty($changes) ? ['changes' => $changes] : null,
                'ip_address' => request()?->ip(),
            ]);
        } catch (\Throwable) {
            // Silently fail — don't break the app if logging fails
        }
    }
}
