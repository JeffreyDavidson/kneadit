<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
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
            ->map(function (Product $product): ?array {
                $recipe = $product->recipe;

                if (! $recipe instanceof Recipe) {
                    return null;
                }

                $margin = (float) (RecipePresenter::for($recipe)->profitMargin() ?? 0);

                if ($margin >= 30) {
                    return null;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    // Money's __toString is "$X.XX" which parses as 0 in numeric context;
                    // formatted() / dollars() are the safe accessors.
                    'price' => $product->price?->formatted() ?? '—',
                    'cost' => $product->cost?->formatted() ?? '—',
                    'margin' => $margin,
                    'margin_formatted' => Number::format($margin, 1) . '%',
                ];
            })
            ->filter()
            ->sortBy('margin')
            ->values()
            ->all());
    }

    protected function cachePrefix(): string
    {
        return 'margin_alert_widget';
    }
}
