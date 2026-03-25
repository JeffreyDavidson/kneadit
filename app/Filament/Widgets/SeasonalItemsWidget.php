<?php

namespace App\Filament\Widgets;

use App\Models\SeasonalItem;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class SeasonalItemsWidget extends Widget
{
    protected static ?int $sort = 22;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.seasonal-items-widget';

    public function getCurrentlyInSeasonCount(): int
    {
        return SeasonalItem::current()->count();
    }

    /** @return array<string, mixed> */
    public function getComingSoon(): array
    {
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
    }

    /** @return array<int, array<string, string>> */
    public function getEndingSoon(): array
    {
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
    }
}
