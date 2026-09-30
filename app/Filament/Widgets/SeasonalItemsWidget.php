<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Inventory\SeasonalItem;
use App\Services\Scheduling\BakeryClock;
use Filament\Widgets\Widget;

class SeasonalItemsWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    #[\Override]
    protected static ?int $sort = 22;

    #[\Override]
    protected string $view = 'filament.widgets.seasonal-items-widget';

    public function getCurrentlyInSeasonCount(): int
    {
        $today = resolve(BakeryClock::class)->today();

        return $this->cached("in_season_{$today->toDateString()}", [3600, 7200], fn (): int => SeasonalItem::current()->count());
    }

    /** @return array<int, array<string, mixed>> */
    public function getComingSoon(): array
    {
        $today = resolve(BakeryClock::class)->today();

        return $this->cached("coming_{$today->toDateString()}", [3600, 7200], fn (): array => SeasonalItem::with('product')
            ->whereDate('available_from', '>', $today)
            ->whereDate('available_from', '<=', $today->copy()->addDays(14))
            ->orderBy('available_from')
            ->limit(5)
            ->get()
            ->map(fn (SeasonalItem $s): array => [
                'name' => $s->product->name ?? 'Unknown',
                'date' => $s->available_from->format('M j'),
            ])
            ->all());
    }

    /** @return array<int, array<string, mixed>> */
    public function getEndingSoon(): array
    {
        $today = resolve(BakeryClock::class)->today();

        return $this->cached("ending_{$today->toDateString()}", [3600, 7200], fn (): array => SeasonalItem::with('product')
            ->whereDate('available_until', '>=', $today)
            ->whereDate('available_until', '<=', $today->copy()->addDays(14))
            ->orderBy('available_until')
            ->limit(5)
            ->get()
            ->map(fn (SeasonalItem $s): array => [
                'name' => $s->product->name ?? 'Unknown',
                'date' => $s->available_until->format('M j'),
            ])
            ->all());
    }

    protected function cachePrefix(): string
    {
        return 'seasonal_items';
    }
}
