<?php

namespace App\Filament\Resources\LoyaltyRewards\Schemas;

use App\Enums\Engagement\RewardType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LoyaltyRewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->rows(3),
            TextInput::make('points_required')
                ->required()
                ->numeric()
                ->minValue(1),
            Select::make('reward_type')
                ->required()
                ->options(RewardType::class)
                ->live(),
            TextInput::make('reward_value')
                ->required()
                ->numeric()
                ->minValue(0)
                ->label(fn (Get $get) => match ($get('reward_type')) {
                    RewardType::PercentageDiscount->value => 'Discount Percentage (%)',
                    RewardType::FixedDiscount->value => 'Discount Amount ($)',
                    default => 'Reward Value',
                }),
            Select::make('product_id')
                ->label('Product')
                ->relationship('product', 'name')
                ->searchable()
                ->visible(fn (Get $get) => $get('reward_type') === RewardType::FreeProduct->value),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }
}
