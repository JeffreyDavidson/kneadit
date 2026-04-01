<?php

namespace App\Models\Operations;

use Database\Factories\Operations\BusinessScheduleFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $day_name
 *
 * @method static \Database\Factories\BusinessScheduleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessSchedule query()
 *
 * @mixin \Eloquent
 */
class BusinessSchedule extends Model
{
    /** @use HasFactory<BusinessScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'is_open',
        'open_time',
        'close_time',
        'order_cutoff_time',
        'max_orders',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_open' => 'boolean',
            'max_orders' => 'integer',
        ];
    }

    public const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    /** @return Attribute<string, never> */
    protected function dayName(): Attribute
    {
        return Attribute::make(
            get: fn () => self::DAYS[$this->day_of_week] ?? 'Unknown',
        );
    }

    protected static function newFactory(): BusinessScheduleFactory
    {
        return BusinessScheduleFactory::new();
    }
}
