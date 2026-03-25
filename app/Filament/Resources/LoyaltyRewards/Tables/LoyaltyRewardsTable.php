<?php

namespace App\Filament\Resources\LoyaltyRewards\Tables;

use App\Models\LoyaltyReward;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoyaltyRewardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('points_required')
                    ->sortable()
                    ->label('Points Required'),
                TextColumn::make('reward_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'percentage_discount' => 'Percentage Off',
                        'fixed_discount' => 'Fixed Discount',
                        'free_product' => 'Free Product',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'percentage_discount' => 'info',
                        'fixed_discount' => 'success',
                        'free_product' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('reward_value')
                    ->formatStateUsing(function (mixed $state, LoyaltyReward $record) {
                        return match ($record->reward_type) {
                            'percentage_discount' => $state.'%',
                            'fixed_discount' => '$'.number_format((float) $state, 2),
                            'free_product' => $record->product?->name ?? '-',
                            default => $state,
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
