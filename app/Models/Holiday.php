<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class Holiday extends Model
{
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
        return Order::whereDate('delivery_date', $this->date)->count();
    }

    public static function nearDate(Carbon $date, int $days = 2): ?self
    {
        return static::active()
            ->whereBetween('date', [
                $date->copy()->subDays($days),
                $date->copy()->addDays($days),
            ])
            ->first();
    }
}
