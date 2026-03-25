<?php

namespace App\Filament\Resources\LoyaltyRewards\Schemas;

use App\Models\Product;
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
                ->options([
                    'percentage_discount' => 'Percentage Discount',
                    'fixed_discount' => 'Fixed Discount ($)',
                    'free_product' => 'Free Product',
                ])
                ->live(),
            TextInput::make('reward_value')
                ->required()
                ->numeric()
                ->minValue(0)
                ->label(fn (Get $get) => match ($get('reward_type')) {
                    'percentage_discount' => 'Discount Percentage (%)',
                    'fixed_discount' => 'Discount Amount ($)',
                    default => 'Reward Value',
                }),
            Select::make('product_id')
                ->label('Product')
                ->options(Product::where('is_active', true)->pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $get('reward_type') === 'free_product'),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }
}
