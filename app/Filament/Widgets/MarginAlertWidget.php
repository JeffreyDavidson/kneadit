<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MarginAlertWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Margin Alerts (below 30%)';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Product::query()->whereHas('recipe')->exists();
    }

    public function table(Table $table): Table
    {
        $lowMarginIds = once(fn () => Product::with('recipe.inventoryIngredients')
            ->whereHas('recipe')
            ->get()
            ->filter(fn (Product $product) => ($product->recipe->profit_margin ?? 100) < 30)
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
                TextColumn::make('recipe.profit_margin')
                    ->label('Margin')
                    ->formatStateUsing(fn (mixed $state) => $state !== null ? number_format($state, 1) . '%' : '—')
                    ->badge()
                    ->color('danger'),
            ])
            ->emptyStateHeading('No low-margin products')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
