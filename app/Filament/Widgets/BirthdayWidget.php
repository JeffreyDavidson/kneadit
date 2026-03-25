<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class BirthdayWidget extends Widget
{
    protected static ?int $sort = 12;

    protected string $view = 'filament.widgets.birthday-widget';

    public function getUpcomingBirthdays(): Collection
    {
        $today = now();

        return Customer::query()->whereNotNull('birthday')
            ->get()
            ->map(function (Customer $customer) use ($today) {
                $next = $customer->birthday->copy()->year($today->year);
                if ($next->lt($today->startOfDay())) {
                    $next->addYear();
                }
                $daysUntil = (int) today()->diffInDays($next, false);

                return (object) [
                    'customer' => $customer,
                    'birthday_date' => $customer->birthday->format('M j'),
                    'days_until' => $daysUntil,
                    'is_today' => $daysUntil === 0,
                ];
            })
            ->filter(fn (object $item) => $item->days_until >= 0 && $item->days_until <= 30)
            ->sortBy('days_until')
            ->take(5);
    }
}
