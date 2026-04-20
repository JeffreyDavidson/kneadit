<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
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

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Margin Alerts (below 30%)';

    protected int|string|array $columnSpan = 'full';

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
            ->columns([
                TextColumn::make('name')
                    ->label('Product'),
                TextColumn::make('price')
                    ->money('usd'),
                TextColumn::make('recipe.cost_per_serving')
                    ->label('Cost')
                    ->money('usd'),
                TextColumn::make('margin')
                    ->label('Margin')
                    ->getStateUsing(fn (Product $record): ?float => $record->recipe
                        ? RecipePresenter::for($record->recipe)->profitMargin()
                        : null)
                    ->formatStateUsing(fn (mixed $state) => $state !== null ? (string) Number::format($state, 1) . '%' : '—')
                    ->badge()
                    ->color('danger'),
            ])
            ->emptyStateHeading('No low-margin products')
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->paginated(false);
    }

    protected function cachePrefix(): string
    {
        return 'margin_alert_widget';
    }
}
