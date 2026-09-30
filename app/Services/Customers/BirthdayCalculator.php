<?php

namespace App\Services\Customers;

use App\Services\Scheduling\BakeryClock;
use Illuminate\Support\Carbon;

class BirthdayCalculator
{
    public function __construct(
        private readonly BakeryClock $clock,
    ) {}

    public function hasBirthday(?Carbon $birthday): bool
    {
        return $birthday instanceof Carbon;
    }

    public function isThisMonth(?Carbon $birthday): bool
    {
        return $birthday?->month === $this->clock->today()->month;
    }

    public function isToday(?Carbon $birthday): bool
    {
        return $birthday?->format('m-d') === $this->clock->today()->format('m-d');
    }

    public function daysUntil(?Carbon $birthday): ?int
    {
        if (! $birthday instanceof Carbon) {
            return null;
        }

        $today = $this->clock->today();

        $next = $birthday->copy()->year($today->year);
        if ($next->lt($today)) {
            $next->addYear();
        }

        return (int) $today->diffInDays($next, false);
    }
}
