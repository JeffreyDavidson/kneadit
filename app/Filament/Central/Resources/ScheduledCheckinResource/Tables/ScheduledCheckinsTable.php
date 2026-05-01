<?php

namespace App\Filament\Central\Resources\ScheduledCheckinResource\Tables;

use App\Filament\Actions\AuthorizedDeleteBulkAction;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ScheduledCheckinsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('days_after_signup')
                    ->label('Days After Signup')
                    ->sortable(),
                TextColumn::make('subject')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('days_after_signup', 'asc')
            ->recordActions([
                Actions\EditAction::make()
                    ->slideOver(),
            ])
            ->toolbarActions([
                AuthorizedDeleteBulkAction::make(),
            ]);
    }
}
