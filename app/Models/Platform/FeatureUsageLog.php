<?php

namespace App\Models\Platform;

use Database\Factories\Platform\FeatureUsageLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
 *
 * @property-read int|null $total
 *
 * @mixin \Eloquent
 */
class FeatureUsageLog extends Model
{
    /** @use HasFactory<FeatureUsageLogFactory> */
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

    protected static function newFactory(): FeatureUsageLogFactory
    {
        return FeatureUsageLogFactory::new();
    }
}
