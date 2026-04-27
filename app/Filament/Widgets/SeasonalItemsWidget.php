<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Inventory\SeasonalItem;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class SeasonalItemsWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 22;

    protected string $view = 'filament.widgets.seasonal-items-widget';

    public function getCurrentlyInSeasonCount(): int
    {
        return $this->cached('in_season_' . now()->format('Y-m-d'), [3600, 7200], fn (): int => SeasonalItem::current()->count());
    }

    /** @return array<int, array<string, mixed>> */
    public function getComingSoon(): array
    {
        return $this->cached('coming_' . now()->format('Y-m-d'), [3600, 7200], function (): array {
            return SeasonalItem::with('product')
                ->where('available_from', '>', Date::today())
                ->where('available_from', '<=', Date::today()->addDays(14))
                ->orderBy('available_from')
                ->limit(5)
                ->get()
                ->map(fn (SeasonalItem $s) => [
                    'name' => $s->product->name ?? 'Unknown',
                    'date' => $s->available_from->format('M j'),
                ])
                ->all();
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function getEndingSoon(): array
    {
        return $this->cached('ending_' . now()->format('Y-m-d'), [3600, 7200], function (): array {
            return SeasonalItem::with('product')
                ->where('available_until', '>=', Date::today())
                ->where('available_until', '<=', Date::today()->addDays(14))
                ->orderBy('available_until')
                ->limit(5)
                ->get()
                ->map(fn (SeasonalItem $s) => [
                    'name' => $s->product->name ?? 'Unknown',
                    'date' => $s->available_until->format('M j'),
                ])
                ->all();
        });
    }

    protected function cachePrefix(): string
    {
        return 'seasonal_items';
    }
}
