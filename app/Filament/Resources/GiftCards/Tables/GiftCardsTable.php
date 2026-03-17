<?php

namespace App\Filament\Resources\GiftCards\Tables;

use App\Enums\GiftCardStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GiftCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                TextColumn::make('initial_balance')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('current_balance')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('purchaser_name')
                    ->label('Purchaser')
                    ->searchable(),

                TextColumn::make('recipient_name')
                    ->label('Recipient')
                    ->searchable()
                    ->placeholder('—'),

                BadgeColumn::make('status')
                    ->getStateUsing(fn ($record) => $record->status->value)
                    ->colors([
                        'success' => GiftCardStatus::Active->value,
                        'danger' => GiftCardStatus::Expired->value,
                        'warning' => GiftCardStatus::Depleted->value,
                        'secondary' => GiftCardStatus::Inactive->value,
                    ]),

                TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(GiftCardStatus::class)
                    ->query(function ($query, $state) {
                        return match ($state['value'] ?? null) {
                            GiftCardStatus::Active->value => $query->where('is_active', true)
                                ->where('current_balance', '>', 0)
                                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())),
                            GiftCardStatus::Inactive->value => $query->where('is_active', false),
                            GiftCardStatus::Depleted->value => $query->where('current_balance', '<=', 0),
                            GiftCardStatus::Expired->value => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
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
