<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class LoyaltySection
{
    public static function make(): Section
    {
        return Section::make('Loyalty Program')
            ->description('Configure how customers earn points and unlock tiers')
            ->schema([
                TextInput::make('loyalty_program_name')
                    ->label('Program Name')
                    ->required()
                    ->maxLength(255)
                    ->default('Rewards')
                    ->helperText('Shown to customers on the storefront and in loyalty emails.'),

                TextInput::make('loyalty_points_per_dollar')
                    ->label('Points Earned per Dollar')
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
                    ->helperText('Points a customer earns for every dollar spent.'),

                Toggle::make('loyalty_tiers_enabled')
                    ->label('Enable Loyalty Tiers')
                    ->helperText('Group customers into Silver, Gold and Platinum by lifetime points.')
                    ->live()
                    ->columnSpanFull(),

                Grid::make(3)
                    ->visible(fn (Get $get): bool => (bool) $get('loyalty_tiers_enabled'))
                    ->schema([
                        TextInput::make('loyalty_tier_silver_threshold')
                            ->label('Silver Threshold (points)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->default(500),

                        TextInput::make('loyalty_tier_gold_threshold')
                            ->label('Gold Threshold (points)')
                            ->numeric()
                            ->required()
                            ->gt('loyalty_tier_silver_threshold')
                            ->default(2000),

                        TextInput::make('loyalty_tier_platinum_threshold')
                            ->label('Platinum Threshold (points)')
                            ->numeric()
                            ->required()
                            ->gt('loyalty_tier_gold_threshold')
                            ->default(5000),

                        Toggle::make('loyalty_tier_perks_enabled')
                            ->label('Enable Tier Perks')
                            ->helperText('Give higher tiers a points multiplier and free delivery.')
                            ->live()
                            ->columnSpanFull(),
                    ]),

                Grid::make(3)
                    ->visible(fn (Get $get): bool => (bool) $get('loyalty_tiers_enabled') && (bool) $get('loyalty_tier_perks_enabled'))
                    ->schema([
                        TextInput::make('loyalty_tier_silver_multiplier')
                            ->label('Silver Points Multiplier')
                            ->numeric()
                            ->minValue(1)
                            ->step(0.1)
                            ->required()
                            ->default(1.0),

                        TextInput::make('loyalty_tier_gold_multiplier')
                            ->label('Gold Points Multiplier')
                            ->numeric()
                            ->minValue(1)
                            ->step(0.1)
                            ->required()
                            ->default(1.5),

                        TextInput::make('loyalty_tier_platinum_multiplier')
                            ->label('Platinum Points Multiplier')
                            ->numeric()
                            ->minValue(1)
                            ->step(0.1)
                            ->required()
                            ->default(2.0),

                        Toggle::make('loyalty_tier_silver_free_delivery')
                            ->label('Silver Free Delivery'),

                        Toggle::make('loyalty_tier_gold_free_delivery')
                            ->label('Gold Free Delivery'),

                        Toggle::make('loyalty_tier_platinum_free_delivery')
                            ->label('Platinum Free Delivery'),
                    ]),
            ]);
    }
}
