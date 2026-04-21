<?php

namespace App\Models\Platform;

use Database\Factories\Platform\FeatureUsageLogFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureUsageLog query()
 *
 * @property-read int|null $total
 *
 * @mixin \Eloquent
 */
#[Table('feature_usage_logs')]
#[WithoutTimestamps]
#[Connection('central')]
#[Fillable('tenant_id', 'feature', 'usage_count', 'last_used_at', 'date', 'created_at')]
#[UseFactory(FeatureUsageLogFactory::class)]
class FeatureUsageLog extends Model
{
    /** @use HasFactory<FeatureUsageLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
            'last_used_at' => 'datetime',
            'date' => 'date',
            'created_at' => 'datetime',
        ];
    }
}
