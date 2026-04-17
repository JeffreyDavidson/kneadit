<?php

namespace App\Filament\Resources\Recipes\Tables;

use App\Filament\Actions\SlideOverEditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecipesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product']))
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable()
                    ->searchable()
                    ->placeholder('No product linked'),

                TextColumn::make('prep_time_minutes')
                    ->label('Prep Time')
                    ->formatStateUsing(fn (?int $state) => $state ? $state . ' min' : '-')
                    ->sortable(),

                TextColumn::make('cost')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('ingredients')
                    ->formatStateUsing(function (mixed $state) {
                        if (is_array($state)) {
                            return collect($state)->take(3)->pluck('name')->join(', ') .
                                   (count($state) > 3 ? '...' : '');
                        }

                        return '-';
                    })
                    ->limit(50),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Product'),
            ])
            ->recordActions([
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No recipes yet')
            ->emptyStateDescription('Add recipes to track ingredient costs and manage production.');
    }
}
