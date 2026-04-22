<?php

namespace App\Presenters;

use App\Models\Operations\Holiday;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

final class HolidayPresenter
{
    public function __construct(
        public readonly Holiday $holiday,
    ) {}

    public static function for(Holiday $holiday): self
    {
        return new self($holiday);
    }

    public function daysAway(): int
    {
        return (int) Date::today()->diffInDays($this->holiday->date, false);
    }

    public function startPrepBy(): Carbon
    {
        return $this->holiday->date->copy()->subDays($this->holiday->lead_days);
    }

    public function isUpcoming(): bool
    {
        return $this->holiday->date->isFuture();
    }

    public function isInPrepPeriod(): bool
    {
        return now()->isAfter($this->startPrepBy()) && $this->holiday->date->isFuture();
    }

    public function daysUntilDeadline(): int
    {
        return $this->holiday->order_deadline
            ? (int) Date::today()->diffInDays($this->holiday->order_deadline, false)
            : $this->daysAway();
    }

    public function isDeadlinePassed(): bool
    {
        return $this->holiday->order_deadline
            ? $this->holiday->order_deadline->isPast()
            : $this->holiday->date->isPast();
    }

    public function daysAwayLabel(): string
    {
        $days = $this->daysAway();

        return match (true) {
            $days < 0 => 'Passed ' . abs($days) . ' days ago',
            $days === 0 => 'Today!',
            $days === 1 => 'Tomorrow',
            default => $days . ' days away',
        };
    }

    public function prepStatus(): string
    {
        $days = $this->daysAway();

        return match (true) {
            $days < 0 => 'Passed',
            $this->isInPrepPeriod() => 'Prep Time!',
            $days <= $this->holiday->lead_days => 'Urgent',
            $days <= $this->holiday->lead_days * 2 => 'Coming Up',
            default => 'Planning',
        };
    }

    public function prepStatusColor(): string
    {
        $days = $this->daysAway();

        return match (true) {
            $days < 0 => 'gray',
            $this->isInPrepPeriod() => 'yellow',
            $days <= $this->holiday->lead_days => 'red',
            $days <= $this->holiday->lead_days * 2 => 'orange',
            default => 'green',
        };
    }

    public function orderingStatus(): string
    {
        if ($this->holiday->date->isPast()) {
            return 'Past';
        }
        if ($this->isDeadlinePassed()) {
            return 'Deadline passed';
        }
        if ($this->daysUntilDeadline() <= 3) {
            return 'Urgent';
        }

        return 'Open';
    }

    public function orderingStatusColor(): string
    {
        return match ($this->orderingStatus()) {
            'Past' => 'gray',
            'Deadline passed' => 'danger',
            'Urgent' => 'warning',
            default => 'success',
        };
    }
}
