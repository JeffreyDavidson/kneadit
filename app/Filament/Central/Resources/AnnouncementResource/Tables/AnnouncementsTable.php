<?php

namespace App\Filament\Central\Resources\AnnouncementResource\Tables;

use App\Enums\AnnouncementType;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (AnnouncementType $state): string => $state->color()),
                TextColumn::make('target_plans')
                    ->label('Target')
                    ->formatStateUsing(fn (mixed $state) => empty($state) ? 'All Plans' : (is_array($state) ? implode(', ', $state) : 'All Plans'))
                    ->badge(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                EditAction::make()
                    ->slideOver(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
