<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Inventory\Product;
use App\Presenters\RecipePresenter;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Number;

class MarginAlertWidget extends BaseWidget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Margin Alerts (below 30%)';

    public static function canView(): bool
    {
        return Product::query()->whereHas('recipe')->exists();
    }

    public function table(Table $table): Table
    {
        $lowMarginIds = $this->cached('low_margin_ids', [300, 600], fn (): array => Product::with('recipe.inventoryIngredients')
            ->whereHas('recipe')
            ->get()
            ->filter(fn (Product $product) => $product->recipe
                && (RecipePresenter::for($product->recipe)->profitMargin() ?? 100) < 30)
            ->pluck('id')
            ->all());

        return $table
            ->query(
                Product::query()->whereIn('id', $lowMarginIds ?: [0]),
            )
            ->columns($this->columnSet())
            ->emptyStateHeading('No low-margin products')
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->paginated(false);
    }

    /** @return array<int, TextColumn> */
    private function columnSet(): array
    {
        // margin_alert is constrained to sm/md in WidgetMeta — at sm we show
        // the alert signal only (product + margin), md adds price + cost
        // for context.
        $marginColumn = TextColumn::make('margin')
            ->label('Margin')
            ->getStateUsing(fn (Product $record): ?float => $record->recipe
                ? RecipePresenter::for($record->recipe)->profitMargin()
                : null)
            ->formatStateUsing(fn (mixed $state) => $state !== null ? (string) Number::format($state, 1) . '%' : '—')
            ->badge()
            ->color('danger');

        if ($this->isSize('sm')) {
            return [
                TextColumn::make('name')->label('Product'),
                $marginColumn,
            ];
        }

        return [
            TextColumn::make('name')->label('Product'),
            TextColumn::make('price')->money('usd'),
            TextColumn::make('recipe.cost_per_serving')->label('Cost')->money('usd'),
            $marginColumn,
        ];
    }

    protected function cachePrefix(): string
    {
        return 'margin_alert_widget';
    }
}
