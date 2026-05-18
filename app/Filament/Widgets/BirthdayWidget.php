<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Customers\Customer;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use stdClass;

class BirthdayWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 12;

    protected string $view = 'filament.widgets.birthday-widget';

    /**
     * Hide on bakeries that have no birthday data at all — the
     * "next 30 days" empty state was just dead space for stores
     * that never collect birthdays. Coarse check (any customer with
     * a birthday set, regardless of date) — accepts that the
     * widget may still occasionally show "no upcoming" between
     * birthday clusters; that's fine because the widget exists for
     * stores that DO use the birthday program.
     */
    public static function canView(): bool
    {
        return Customer::query()->whereNotNull('birthday')->exists();
    }

    /** @return Collection<int, stdClass> */
    public function getUpcomingBirthdays(): Collection
    {
        $limit = match ($this->size()) {
            WidgetSize::Small => 3,
            WidgetSize::Medium => 5,
            WidgetSize::Large, WidgetSize::ExtraLarge => 10,
        };

        // Cache plain arrays of denormalized fields, not Customer models / stdClass
        // objects. Cache stores hydrate as __PHP_Incomplete_Class because
        // config(cache.serializable_classes) is false. Same shape as #302.
        $entries = $this->cached("upcoming_{$limit}", [3600, 7200], function () use ($limit): array {
            $today = now();

            return Customer::query()->whereNotNull('birthday')
                ->get()
                ->map(function (Customer $customer) use ($today): ?array {
                    $next = $customer->birthday?->copy()->year($today->year);
                    if (! $next) {
                        return null;
                    }
                    if ($next->lt($today->startOfDay())) {
                        $next->addYear();
                    }
                    $daysUntil = (int) today()->diffInDays($next, false);

                    return [
                        'customer_name' => $customer->name,
                        'birthday_date' => $customer->birthday->format('M j'),
                        'days_until' => $daysUntil,
                        'is_today' => $daysUntil === 0,
                    ];
                })
                ->filter(fn (?array $item): bool => $item !== null && $item['days_until'] >= 0 && $item['days_until'] <= 30)
                ->sortBy('days_until')
                ->take($limit)
                ->values()
                ->all();
        });

        return collect(array_map(fn (array $entry): stdClass => (object) $entry, $entries));
    }

    protected function cachePrefix(): string
    {
        return 'birthday_widget';
    }
}
