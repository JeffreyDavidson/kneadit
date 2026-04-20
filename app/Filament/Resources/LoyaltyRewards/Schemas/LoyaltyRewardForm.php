<?php

namespace App\Filament\Resources\LoyaltyRewards\Schemas;

use App\Enums\Engagement\RewardType;
use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Forms\Components\PercentageInput;
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

            PercentageInput::make('discount_percentage')
                ->label('Discount Percentage')
                ->helperText('Use for percentage discounts')
                ->required(fn (Get $get) => $get('reward_type') === RewardType::PercentageDiscount->value),

            MoneyInput::make('discount_amount')
                ->label('Discount Amount')
                ->helperText('Use for fixed-amount discounts')
                ->required(fn (Get $get) => $get('reward_type') === RewardType::FixedDiscount->value),

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
