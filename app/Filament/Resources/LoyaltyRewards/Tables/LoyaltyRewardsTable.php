<?php

namespace App\Filament\Resources\LoyaltyRewards\Tables;

use App\Enums\Engagement\RewardType;
use App\Models\Engagement\LoyaltyReward;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

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
                TextColumn::make('reward_value')
                    ->formatStateUsing(function (mixed $state, LoyaltyReward $record) {
                        $type = $record->reward_type;

                        return match ($type) {
                            RewardType::PercentageDiscount => $state . '%',
                            RewardType::FixedDiscount => Number::currency((float) $state),
                            RewardType::FreeProduct => $record->product->name ?? '-',
                        };
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
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->defaultSort('points_required')
            ->emptyStateHeading('No loyalty rewards yet')
            ->emptyStateDescription('Create rewards for your loyalty program.');
    }
}
