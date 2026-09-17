<?php

namespace App\Services\Customers;

use Illuminate\Support\Carbon;

class BirthdayCalculator
{
    public function hasBirthday(?Carbon $birthday): bool
    {
        return $birthday instanceof Carbon;
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
        if (! $birthday instanceof Carbon) {
            return null;
        }

        $next = $birthday->copy()->year(now()->year);
        if ($next->isPast()) {
            $next->addYear();
        }

        return (int) now()->diffInDays($next, false);
    }
}
