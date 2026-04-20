<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Customers\Customer;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class BirthdayWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = 12;

    protected string $view = 'filament.widgets.birthday-widget';

    /** @return Collection<int, mixed> */
    public function getUpcomingBirthdays(): Collection
    {
        return $this->cached('upcoming', [3600, 7200], function (): Collection {
            $today = now();

            return Customer::query()->whereNotNull('birthday')
                ->get()
                ->map(function (Customer $customer) use ($today) {
                    $next = $customer->birthday?->copy()->year($today->year);
                    if (! $next) {
                        return null;
                    }
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
                ->filter(fn (mixed $item): bool => is_object($item) && $item->days_until >= 0 && $item->days_until <= 30)
                ->sortBy('days_until')
                ->take(5);
        });
    }

    protected function cachePrefix(): string
    {
        return 'birthday_widget';
    }
}
