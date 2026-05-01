<?php

namespace App\Filament\Resources\GiftCards\Tables;

use App\Enums\Financial\GiftCardStatus;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Tables\Columns\MoneyColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
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

                MoneyColumn::make('initial_balance')
                    ->sortable(),

                MoneyColumn::make('current_balance')
                    ->sortable(),

                TextColumn::make('purchaser_name')
                    ->label('Purchaser')
                    ->searchable(),

                TextColumn::make('recipient_name')
                    ->label('Recipient')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge(),

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
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No gift cards yet')
            ->emptyStateDescription('Gift cards purchased by customers will appear here.');
    }
}
