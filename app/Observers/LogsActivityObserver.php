<?php

namespace App\Observers;

use App\Enums\Operations\ActivityAction;
use App\Models\Operations\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class LogsActivityObserver
{
    public function created(Model $model): void
    {
        $this->log($model, ActivityAction::Created);
    }

    public function updated(Model $model): void
    {
        $this->log($model, ActivityAction::Updated, $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->log($model, ActivityAction::Deleted);
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function log(Model $model, ActivityAction $action, array $changes = []): void
    {
        if ($model instanceof ActivityLog) {
            return;
        }

        try {
            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'System',
                'action' => $action,
                'model_type' => $model::class,
                'model_id' => $model->getKey(),
                'description' => class_basename($model) . " #{$model->getKey()} was {$action->value}",
                'properties' => ! empty($changes) ? ['changes' => $changes] : null,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Activity log failed', [
                'model' => $model::class,
                'id' => $model->getKey(),
                'action' => $action->value,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
