<?php

namespace App\Filament\Resources\GiftCards\Tables;

use App\Enums\GiftCardStatus;
use App\Models\GiftCard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->getStateUsing(fn (GiftCard $record) => $record->status->value)
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
                    ->query(function (Builder $query, array $state) {
                        return match ($state['value'] ?? null) {
                            GiftCardStatus::Active->value => $query->where('is_active', true)
                                ->where('current_balance', '>', 0)
                                ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())),
                            GiftCardStatus::Inactive->value => $query->where('is_active', false),
                            GiftCardStatus::Depleted->value => $query->where('current_balance', '<=', 0),
                            GiftCardStatus::Expired->value => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No gift cards yet')
            ->emptyStateDescription('Gift cards purchased by customers will appear here.');
    }
}
