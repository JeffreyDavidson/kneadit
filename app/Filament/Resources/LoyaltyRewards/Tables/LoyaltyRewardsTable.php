<?php

namespace App\Filament\Resources\LoyaltyRewards\Tables;

use App\Enums\RewardType;
use App\Models\LoyaltyReward;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoyaltyRewardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product']))
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('points_required')
                    ->sortable()
                    ->label('Points Required'),
                TextColumn::make('reward_type')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state) => $state instanceof RewardType ? $state->label() : (RewardType::tryFrom($state)?->label() ?? $state))
                    ->color(fn (mixed $state) => $state instanceof RewardType ? $state->color() : (RewardType::tryFrom($state)?->color() ?? 'gray')),
                TextColumn::make('reward_value')
                    ->formatStateUsing(function (mixed $state, LoyaltyReward $record) {
                        $type = $record->reward_type;

                        return match ($type) {
                            RewardType::PercentageDiscount => $state . '%',
                            RewardType::FixedDiscount => '$' . number_format((float) $state, 2),
                            RewardType::FreeProduct => $record->product->name ?? '-',
                        };
                    })
                    ->label('Value'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->defaultSort('points_required');
    }
}
