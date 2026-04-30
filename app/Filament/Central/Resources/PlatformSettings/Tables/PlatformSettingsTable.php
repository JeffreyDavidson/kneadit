<?php

namespace App\Filament\Central\Resources\PlatformSettings\Tables;

use App\Filament\Actions\AuthorizedDeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlatformSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->label('Value')
                    ->limit(80)
                    ->wrap()
                    ->placeholder('—')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('key', 'asc')
            ->emptyStateHeading('No platform settings yet')
            ->emptyStateDescription('Use the New Setting button above to add the first key/value.')
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ]);
    }
}
