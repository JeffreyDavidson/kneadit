<?php

namespace App\Filament\Resources\LoyaltyRewards\Tables;

use App\Enums\Engagement\RewardType;
use App\Filament\Actions\SlideOverEditAction;
use App\Models\Engagement\LoyaltyReward;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                    ->badge(),
                TextColumn::make('discount_value')
                    ->getStateUsing(fn (LoyaltyReward $record): string => match ($record->reward_type) {
                        RewardType::PercentageDiscount => (string) $record->discount_percentage,
                        RewardType::FixedDiscount => (string) $record->discount_amount,
                        RewardType::FreeProduct => $record->product->name ?? '-',
                    })
                    ->label('Value'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('reward_type')
                    ->options(RewardType::class),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                SlideOverEditAction::make(),
            ])
            ->defaultSort('points_required')
            ->emptyStateHeading('No loyalty rewards yet')
            ->emptyStateDescription('Create rewards for your loyalty program.');
    }
}
