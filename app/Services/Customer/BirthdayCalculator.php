<?php

namespace App\Services\Customer;

use Illuminate\Support\Carbon;

class BirthdayCalculator
{
    public function hasBirthday(?Carbon $birthday): bool
    {
        return $birthday !== null;
    }

    public function isThisMonth(?Carbon $birthday): bool
    {
        return $birthday?->month === now()->month;
    }

    public function isToday(?Carbon $birthday): bool
    {
        return $birthday?->format('m-d') === now()->format('m-d');
    }

    public function daysUntil(?Carbon $birthday): ?int
    {
        if (! $birthday) {
            return null;
        }

        $next = $birthday->copy()->year(now()->year);
        if ($next->isPast()) {
            $next->addYear();
        }

        return (int) now()->diffInDays($next, false);
    }
}
