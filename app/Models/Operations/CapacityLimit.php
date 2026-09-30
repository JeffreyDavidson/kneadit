<?php

declare(strict_types=1);

namespace App\Models\Operations;

use App\Builders\Operations\CapacityLimitQueryBuilder;
use Database\Factories\Operations\CapacityLimitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A capacity limit applies either to one specific_date or, when that is null,
 * to every week on day_of_week. day_of_week holds a DayOfWeek value but is not
 * cast to the enum: rows saved before the weekday fix hold '0', which the cast
 * would reject on load.
 *
 * @property int $id
 * @property Carbon|null $specific_date
 * @property string|null $day_of_week
 * @property int $max_orders
 * @property bool $is_blocked
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static CapacityLimitQueryBuilder newModelQuery()
 * @method static CapacityLimitQueryBuilder newQuery()
 * @method static CapacityLimitQueryBuilder query()
 *
 * @mixin \Eloquent
 */
#[Fillable('specific_date', 'day_of_week', 'max_orders', 'is_blocked', 'notes')]
#[UseEloquentBuilder(CapacityLimitQueryBuilder::class)]
#[UseFactory(CapacityLimitFactory::class)]
class CapacityLimit extends Model
{
    /** @use HasFactory<CapacityLimitFactory> */
    use HasFactory;

    #[\Override]
    protected function casts(): array
    {
        return [
            'max_orders' => 'integer',
            'specific_date' => 'date',
            'is_blocked' => 'boolean',
        ];
    }
}
