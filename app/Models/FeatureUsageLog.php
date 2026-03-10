<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FeatureUsageLog extends Model
{
    protected $connection = 'central';

    protected $table = 'feature_usage_logs';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'feature',
        'usage_count',
        'last_used_at',
        'date',
        'created_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'date' => 'date',
        'created_at' => 'datetime',
    ];

    /**
     * Track a feature usage for a tenant. Upserts for today's date.
     */
    public static function track(string $tenantId, string $feature): self
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $log = static::where('tenant_id', $tenantId)
            ->where('feature', $feature)
            ->where('date', $today)
            ->first();

        if ($log) {
            $log->increment('usage_count');
            $log->update(['last_used_at' => $now]);
        } else {
            $log = static::create([
                'tenant_id' => $tenantId,
                'feature' => $feature,
                'usage_count' => 1,
                'last_used_at' => $now,
                'date' => $today,
                'created_at' => $now,
            ]);
        }

        return $log;
    }
}
