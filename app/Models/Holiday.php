<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\HolidayFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $date
 * @property int $lead_days
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $order_deadline
 * @property \Illuminate\Support\Carbon|null $prep_start
 * @property int|null $max_orders
 * @property bool $is_active
 * @property-read int $days_away
 * @property-read bool $is_in_prep_period
 * @property-read bool $is_upcoming
 * @property-read Carbon $start_prep_by
 *
 * @method static Builder<static>|Holiday active()
 * @method static \Database\Factories\HolidayFactory factory($count = null, $state = [])
 * @method static Builder<static>|Holiday newModelQuery()
 * @method static Builder<static>|Holiday newQuery()
 * @method static Builder<static>|Holiday query()
 * @method static Builder<static>|Holiday upcoming()
 *
 * @mixin \Eloquent
 */
class Holiday extends Model
{
    /** @use HasFactory<HolidayFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'lead_days',
        'order_deadline',
        'prep_start',
        'max_orders',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'order_deadline' => 'date',
            'prep_start' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /** @return Attribute<int, never> */
    protected function daysAway(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) now()->diffInDays($this->date, false),
        );
    }

    /** @return Attribute<Carbon, never> */
    protected function startPrepBy(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date->subDays($this->lead_days ?? 7),
        );
    }

    /** @return Attribute<bool, never> */
    protected function isUpcoming(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date->isFuture(),
        );
    }

    /** @return Attribute<bool, never> */
    protected function isInPrepPeriod(): Attribute
    {
        return Attribute::make(
            get: fn () => now()->isAfter($this->start_prep_by) && $this->date->isFuture(),
        );
    }

    /** @param Builder<Holiday> $query */
    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('date', '>=', Date::today())->orderBy('date');
    }

    /** @param Builder<Holiday> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function daysUntilDeadline(): int
    {
        if (! $this->order_deadline) {
            return $this->days_away;
        }

        return (int) Date::today()->diffInDays($this->order_deadline, false);
    }

    public function isDeadlinePassed(): bool
    {
        if (! $this->order_deadline) {
            return $this->date->isPast();
        }

        return $this->order_deadline->isPast();
    }
}
