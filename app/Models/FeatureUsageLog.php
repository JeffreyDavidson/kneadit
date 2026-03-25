<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

/**
 * @property int $id
 * @property string $tenant_id
 * @property string $feature
 * @property int $usage_count
 * @property Carbon|null $last_used_at
 * @property Carbon $date
 * @property Carbon|null $created_at
 *
 * @method static \Database\Factories\FeatureUsageLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog whereFeature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog whereUsageCount($value)
 *
 * @property-read int|null $total
 *
 * @mixin \Eloquent
 */
class FeatureUsageLog extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Track a feature usage for a tenant. Upserts for today's date.
     */
    public static function track(string $tenantId, string $feature): self
    {
        $today = Date::today();
        $now = Date::now();

        $log = static::query()->where('tenant_id', $tenantId)
            ->where('feature', $feature)
            ->whereDate('date', $today)
            ->first();

        if ($log) {
            static::query()->where('id', $log->id)->update([
                'usage_count' => $log->usage_count + 1,
                'last_used_at' => $now,
            ]);
            $log->refresh();
        } else {
            $log = static::query()->create([
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
