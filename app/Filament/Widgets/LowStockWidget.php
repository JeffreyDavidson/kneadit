<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 11;

    protected static ?string $heading = 'Low Stock Ingredients';

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 1];

    public static function canView(): bool
    {
        return Ingredient::query()->where(function (Builder $q) {
            $q->where('current_stock', '<=', 0)
                ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
        })->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ingredient::query()
                    ->where(function (\Illuminate\Contracts\Database\Query\Builder $q) {
                        $q->where('current_stock', '<=', 0)
                            ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
                    })
                    ->orderBy('current_stock')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Ingredient'),

                TextColumn::make('current_stock')
                    ->label('In Stock')
                    ->formatStateUsing(fn (Ingredient $record) => $record->current_stock.' '.$record->unit)
                    ->badge()
                    ->color(fn (Ingredient $record) => $record->isOutOfStock() ? 'danger' : 'warning'),

                TextColumn::make('low_stock_threshold')
                    ->label('Threshold')
                    ->formatStateUsing(fn (Ingredient $record) => $record->low_stock_threshold.' '.$record->unit),

                TextColumn::make('reorder_qty')
                    ->label('Reorder')
                    ->getStateUsing(fn (Ingredient $record) => max(0, $record->low_stock_threshold - $record->current_stock))
                    ->formatStateUsing(fn (mixed $state, Ingredient $record) => $state.' '.$record->unit),

                TextColumn::make('supplier')
                    ->placeholder('—'),
            ])
            ->paginated(false)
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('All stocked up!')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
