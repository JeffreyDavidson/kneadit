<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(fn ($model) => static::logAction($model, 'created'));
        static::updated(fn ($model) => static::logAction($model, 'updated', $model->getChanges()));
        static::deleted(fn ($model) => static::logAction($model, 'deleted'));
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    protected static function logAction(Model $model, string $action, array $changes = []): void
    {
        // Prevent recursive logging
        if ($model instanceof ActivityLog) {
            return;
        }

        try {
            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'user_name' => (string) (auth()->user() ? auth()->user()->name : 'System'),
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => (string) $model->getKey(),
                'description' => class_basename($model).' #'.(string) $model->getKey()." was {$action}",
                'properties' => ! empty($changes) ? ['changes' => $changes] : null,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable) {
            // Silently fail — don't break the app if logging fails
        }
    }
}
