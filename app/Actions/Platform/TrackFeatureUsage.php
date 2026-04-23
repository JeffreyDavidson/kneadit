<?php

namespace App\Actions\Platform;

use App\Models\Platform\FeatureUsageLog;
use Illuminate\Support\Facades\Date;

class TrackFeatureUsage
{
    public function __invoke(string $tenantId, string $feature): FeatureUsageLog
    {
        $today = Date::today();
        $now = Date::now();

        $log = FeatureUsageLog::query()
            ->where('tenant_id', $tenantId)
            ->where('feature', $feature)
            ->whereDate('date', $today)
            ->first();

        if ($log) {
            // Atomic increment — concurrent requests for the same tenant+feature+date
            // would otherwise race (both read N, both write N+1, lose an event).
            FeatureUsageLog::query()
                ->whereKey($log->id)
                ->increment('usage_count', 1, ['last_used_at' => $now]);

            return $log->refresh();
        }

        return FeatureUsageLog::query()->create([
            'tenant_id' => $tenantId,
            'feature' => $feature,
            'usage_count' => 1,
            'last_used_at' => $now,
            'date' => $today,
            'created_at' => $now,
        ]);
    }
}
