<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer_email')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable()
                    ->searchable()
                    ->placeholder('General review'),

                BadgeColumn::make('rating')
                    ->colors([
                        'danger' => fn ($state) => $state <= 2,
                        'warning' => 3,
                        'success' => fn ($state) => $state >= 4,
                    ])
                    ->formatStateUsing(fn ($state) => $state . ' ★'),

                TextColumn::make('comment')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),

                ToggleColumn::make('is_approved')
                    ->label('Approved'),

                ToggleColumn::make('is_featured')
                    ->label('Featured'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),

                SelectFilter::make('is_approved')
                    ->options([
                        1 => 'Approved',
                        0 => 'Pending',
                    ]),

                SelectFilter::make('is_featured')
                    ->options([
                        1 => 'Featured',
                        0 => 'Not Featured',
                    ]),

                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Product'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['is_approved' => true]))
                    ->visible(fn ($record) => !$record->is_approved),

                Action::make('feature')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn ($record) => $record->update(['is_featured' => true]))
                    ->visible(fn ($record) => !$record->is_featured && $record->is_approved),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
