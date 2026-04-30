<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products'),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('sort_order')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->recordActions([
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Create categories to organize your products.');
    }
}
