<?php

namespace App\Filament\Resources\Ingredients\Tables;

use App\Models\Ingredient;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class IngredientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('current_stock')
                    ->label('Stock')
                    ->formatStateUsing(fn (Ingredient $record) => $record->current_stock.' '.$record->unit)
                    ->sortable(),

                TextColumn::make('stock_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Ingredient $record) => $record->getStockStatus())
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'good' => 'Good',
                        'low' => 'Low',
                        'out' => 'Out of Stock',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'good' => 'success',
                        'low' => 'warning',
                        'out' => 'danger',
                    }),

                TextColumn::make('cost_per_unit')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('supplier')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('stock_status')
                    ->label('Status')
                    ->options([
                        'low' => 'Low Stock',
                        'out' => 'Out of Stock',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value'] ?? null) {
                            'low' => $query->where('current_stock', '>', 0)
                                ->whereColumn('current_stock', '<=', 'low_stock_threshold'),
                            'out' => $query->where('current_stock', '<=', 0),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('record_stock')
                    ->label('Record Stock')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        Select::make('type')
                            ->options([
                                'purchase' => 'Purchase (add)',
                                'usage' => 'Usage (subtract)',
                                'waste' => 'Waste (subtract)',
                                'adjustment' => 'Adjustment',
                            ])
                            ->required(),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01),
                        TextInput::make('notes')
                            ->maxLength(255),
                    ])
                    ->action(function (Ingredient $record, array $data) {
                        $qty = (float) $data['quantity'];
                        if (in_array($data['type'], ['usage', 'waste'])) {
                            $qty = -$qty;
                        }
                        $record->adjustStock($qty, $data['type'], $data['notes'] ?? null);
                    }),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('record_purchase')
                        ->label('Record Purchase')
                        ->icon('heroicon-o-shopping-cart')
                        ->form([
                            TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->step(0.01)
                                ->label('Quantity to add'),
                            TextInput::make('notes')
                                ->maxLength(255),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $ingredient) {
                                $ingredient->adjustStock((float) $data['quantity'], 'purchase', $data['notes'] ?? null);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No ingredients yet')
            ->emptyStateDescription('Add your first ingredient to start tracking inventory.');
    }
}
