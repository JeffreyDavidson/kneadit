<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->circular(),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('cost')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('margin')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->cost && $record->price) {
                            return round(($record->price - $record->cost) / $record->price * 100, 2) . '%';
                        }
                        return '-';
                    })
                    ->label('Margin %')
                    ->color(function ($state, $record) {
                        if ($record->cost && $record->price) {
                            $margin = ($record->price - $record->cost) / $record->price * 100;
                            return $margin > 30 ? 'success' : ($margin > 15 ? 'warning' : 'danger');
                        }
                        return 'gray';
                    }),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                ToggleColumn::make('is_featured')
                    ->label('Featured'),

                TextColumn::make('waitlist_count')
                    ->label('Waitlist')
                    ->getStateUsing(fn ($record) => $record->waitlistEntries()->whereNull('notified_at')->count())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} waiting" : '—'),

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
            ->defaultSort('name');
    }
}
