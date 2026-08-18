<?php

namespace App\Filament\Resources\Recipes\Tables;

use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Tables\Columns\MoneyColumn;
use Filament\Actions\BulkActionGroup;
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

                MoneyColumn::make('cost')
                    ->sortable(),

                TextColumn::make('ingredients')
                    ->formatStateUsing(function (mixed $state): string {
                        if (! is_array($state)) {
                            return '-';
                        }

                        $names = [];

                        foreach (array_slice($state, 0, 3) as $ingredient) {
                            if (is_array($ingredient) && is_string($ingredient['name'] ?? null)) {
                                $names[] = $ingredient['name'];
                            }
                        }

                        return implode(', ', $names) . (count($state) > 3 ? '...' : '');
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
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No recipes yet')
            ->emptyStateDescription('Add recipes to track ingredient costs and manage production.');
    }
}
