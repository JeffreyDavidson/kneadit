<?php

namespace App\Models\Operations;

use App\Enums\Staff\DayOfWeek;
use Database\Factories\Operations\BusinessScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $day_name
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessSchedule query()
 *
 * @mixin \Eloquent
 */
#[Fillable('day_of_week', 'is_open', 'open_time', 'close_time', 'order_cutoff_time', 'max_orders')]
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
