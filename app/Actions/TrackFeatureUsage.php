<?php

namespace App\Actions;

use App\Models\FeatureUsageLog;
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
            FeatureUsageLog::query()->where('id', $log->id)->update([
                'usage_count' => $log->usage_count + 1,
                'last_used_at' => $now,
            ]);
            $log->refresh();

            return $log;
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
