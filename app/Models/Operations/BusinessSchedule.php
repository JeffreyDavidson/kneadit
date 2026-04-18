<?php

namespace App\Models\Operations;

use App\Builders\Operations\BusinessScheduleQueryBuilder;
use App\Enums\Staff\DayOfWeek;
use Database\Factories\Operations\BusinessScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $day_name
 *
 * @method static BusinessScheduleQueryBuilder newModelQuery()
 * @method static BusinessScheduleQueryBuilder newQuery()
 * @method static BusinessScheduleQueryBuilder query()
 *
 * @mixin \Eloquent
 */
#[Fillable('day_of_week', 'is_open', 'open_time', 'close_time', 'order_cutoff_time', 'max_orders')]
#[UseEloquentBuilder(BusinessScheduleQueryBuilder::class)]
class BusinessSchedule extends Model
{
    /** @use HasFactory<BusinessScheduleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_open' => 'boolean',
            'max_orders' => 'integer',
        ];
    }

    /** @return Attribute<string, never> */
    protected function dayName(): Attribute
    {
        return Attribute::make(
            get: fn () => DayOfWeek::fromPhpDayIndex($this->day_of_week)?->getLabel() ?? 'Unknown',
        );
    }

    protected static function newFactory(): BusinessScheduleFactory
    {
        return BusinessScheduleFactory::new();
    }
}
