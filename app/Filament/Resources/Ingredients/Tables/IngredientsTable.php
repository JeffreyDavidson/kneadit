<?php

namespace App\Filament\Resources\Ingredients\Tables;

use App\Actions\Inventory\AdjustIngredientStock;
use App\Enums\Inventory\StockAdjustmentType;
use App\Enums\Inventory\StockStatus;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Tables\Columns\MoneyColumn;
use App\Models\Inventory\Ingredient;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

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
                    ->formatStateUsing(fn (Ingredient $record) => $record->current_stock . ' ' . $record->unit)
                    ->sortable(),

                TextColumn::make('stock_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Ingredient $record) => StockStatus::resolve($record)),

                MoneyColumn::make('cost_per_unit')
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
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->authorize('update')
                    ->schema([
                        Select::make('type')
                            ->options(StockAdjustmentType::class)
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
                        $qty = Arr::float($data, 'quantity');
                        $type = StockAdjustmentType::from(Arr::string($data, 'type'));
                        $notes = Arr::string($data, 'notes', '');
                        if (in_array($type, [StockAdjustmentType::Usage, StockAdjustmentType::Waste])) {
                            $qty = -$qty;
                        }
                        resolve(AdjustIngredientStock::class)($record, $qty, $type, $notes !== '' ? $notes : null);
                    }),
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('record_purchase')
                        ->label('Record Purchase')
                        ->icon(Heroicon::OutlinedShoppingCart)
                        ->schema([
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
                            $quantity = Arr::float($data, 'quantity');
                            $notes = Arr::string($data, 'notes', '');
                            /** @var Collection<int, Ingredient> $records */
                            foreach ($records as $ingredient) {
                                resolve(AdjustIngredientStock::class)($ingredient, $quantity, StockAdjustmentType::Purchase, $notes !== '' ? $notes : null);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No ingredients yet')
            ->emptyStateDescription('Add your first ingredient to start tracking inventory.');
    }
}
