<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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
 * @method static Builder<static>|Holiday whereCreatedAt($value)
 * @method static Builder<static>|Holiday whereDate($value)
 * @method static Builder<static>|Holiday whereId($value)
 * @method static Builder<static>|Holiday whereIsActive($value)
 * @method static Builder<static>|Holiday whereLeadDays($value)
 * @method static Builder<static>|Holiday whereMaxOrders($value)
 * @method static Builder<static>|Holiday whereName($value)
 * @method static Builder<static>|Holiday whereNotes($value)
 * @method static Builder<static>|Holiday whereOrderDeadline($value)
 * @method static Builder<static>|Holiday wherePrepStart($value)
 * @method static Builder<static>|Holiday whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Holiday extends Model
{
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

    protected function getDaysAwayAttribute(): int
    {
        return now()->diffInDays($this->date, false);
    }

    protected function getStartPrepByAttribute(): Carbon
    {
        return $this->date->subDays($this->lead_days ?? 7);
    }

    protected function getIsUpcomingAttribute(): bool
    {
        return $this->date->isFuture();
    }

    protected function getIsInPrepPeriodAttribute(): bool
    {
        return now()->isAfter($this->start_prep_by) && $this->date->isFuture();
    }

    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('date', '>=', Date::today())->orderBy('date');
    }

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

    public function orderCount(): int
    {
        return Order::query()->whereDate('delivery_date', $this->date)->count();
    }

    public static function nearDate(Carbon $date, int $days = 2): ?self
    {
        return static::query()->active()
            ->whereBetween('date', [
                $date->copy()->subDays($days),
                $date->copy()->addDays($days),
            ])
            ->first();
    }
}
