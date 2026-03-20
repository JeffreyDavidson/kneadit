<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'waitlistEntries' => fn (Builder $q) => $q->whereNull('notified_at'),
            ]))
            ->columns([
                ImageColumn::make('image')
                    ->circular(),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('cost')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('margin')
                    ->formatStateUsing(function (mixed $state, Product $record) {
                        if ($record->cost && $record->price) {
                            return round(($record->price - $record->cost) / $record->price * 100, 2).'%';
                        }

                        return '-';
                    })
                    ->label('Margin %')
                    ->color(function ($state, Product $record) {
                        if ($record->cost && $record->price) {
                            $margin = ($record->price - $record->cost) / $record->price * 100;

                            return $margin > 30 ? 'success' : ($margin > 15 ? 'warning' : 'danger');
                        }

                        return 'gray';
                    })
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->toggleable(),

                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->toggleable(),

                TextColumn::make('waitlist_count')
                    ->label('Waitlist')
                    ->getStateUsing(fn (Product $record) => $record->waitlist_entries_count)
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'warning' : 'gray')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? "{$state} waiting" : '—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),

                SelectFilter::make('is_active')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),

                SelectFilter::make('is_featured')
                    ->options([
                        1 => 'Featured',
                        0 => 'Not Featured',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->deferColumnManager(false)
            ->defaultSort('name')
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Add your first product to start building your catalog.');
    }
}
