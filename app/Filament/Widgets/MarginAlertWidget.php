<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Inventory\Product;
use App\Presenters\RecipePresenter;
use Filament\Widgets\Widget;
use Illuminate\Support\Number;

class MarginAlertWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.margin-alert';

    public static function canView(): bool
    {
        return Product::query()->whereHas('recipe')->exists();
    }

    /** @return array<int, array<string, mixed>> */
    public function getRows(): array
    {
        return $this->cached('rows', [300, 600], fn (): array => Product::with('recipe.inventoryIngredients')
            ->whereHas('recipe')
            ->get()
            ->filter(fn (Product $product): bool => $product->recipe
                && (RecipePresenter::for($product->recipe)->profitMargin() ?? 100) < 30)
            ->sortBy(fn (Product $product): float => RecipePresenter::for($product->recipe)->profitMargin() ?? 0)
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                // Money's __toString is "$X.XX" which parses as 0 in numeric context;
                // formatted() / dollars() are the safe accessors.
                'price' => $product->price?->formatted() ?? '—',
                'cost' => $product->cost?->formatted() ?? '—',
                'margin' => (float) (RecipePresenter::for($product->recipe)->profitMargin() ?? 0),
                'margin_formatted' => Number::format((float) (RecipePresenter::for($product->recipe)->profitMargin() ?? 0), 1) . '%',
            ])
            ->values()
            ->all());
    }

    protected function cachePrefix(): string
    {
        return 'margin_alert_widget';
    }
}
