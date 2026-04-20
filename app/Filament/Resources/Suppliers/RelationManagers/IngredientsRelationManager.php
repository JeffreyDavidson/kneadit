<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IngredientsRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredients';

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                TextInput::make('name')
                    ->required()
                    ->disabled(),

                MoneyInput::make('unit_price'),

                TextInput::make('minimum_order')
                    ->numeric()
                    ->step(0.01),

                TextInput::make('lead_time_days')
                    ->label('Lead Time (days)')
                    ->numeric()
                    ->integer(),

                TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit')
                    ->toggleable(),

                TextColumn::make('unit_price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('minimum_order')
                    ->label('Min Order')
                    ->sortable(),

                TextColumn::make('lead_time_days')
                    ->label('Lead Time')
                    ->suffix(' days')
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        MoneyInput::make('unit_price'),
                        TextInput::make('minimum_order')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('lead_time_days')
                            ->label('Lead Time (days)')
                            ->numeric()
                            ->integer(),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
