<?php

namespace App\Filament\Widgets;

use App\Models\SeasonalItem;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class SeasonalItemsWidget extends Widget
{
    protected static ?int $sort = 22;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.seasonal-items-widget';

    public function getCurrentlyInSeasonCount(): int
    {
        return SeasonalItem::current()->count();
    }

    public function getComingSoon(): array
    {
        return SeasonalItem::with('product')
            ->where('available_from', '>', Carbon::today())
            ->where('available_from', '<=', Carbon::today()->addDays(14))
            ->orderBy('available_from')
            ->limit(5)
            ->get()
            ->map(fn ($s) => [
                'name' => $s->product?->name ?? 'Unknown',
                'date' => $s->available_from->format('M j'),
            ])
            ->toArray();
    }

    public function getEndingSoon(): array
    {
        return SeasonalItem::with('product')
            ->where('available_until', '>=', Carbon::today())
            ->where('available_until', '<=', Carbon::today()->addDays(14))
            ->orderBy('available_until')
            ->limit(5)
            ->get()
            ->map(fn ($s) => [
                'name' => $s->product?->name ?? 'Unknown',
                'date' => $s->available_until->format('M j'),
            ])
            ->toArray();
    }
}
