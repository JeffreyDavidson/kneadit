<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class LoyaltyTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Loyalty')
            ->schema([
                TextInput::make('pageContent.loyalty.hero_eyebrow')
                    ->label('Hero Eyebrow'),
                TextInput::make('pageContent.loyalty.hero_subtitle')
                    ->label('Hero Subtitle'),
                TextInput::make('pageContent.loyalty.paused_message')
                    ->label('Program Paused Message'),
                TextInput::make('pageContent.loyalty.check_heading')
                    ->label('Check Points Heading'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.loyalty.rewards_eyebrow')
                        ->label('Rewards Eyebrow'),
                    TextInput::make('pageContent.loyalty.rewards_heading')
                        ->label('Rewards Heading'),
                ]),
                Section::make('How It Works')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.loyalty.how_it_works_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.loyalty.how_it_works_heading')
                            ->label('Heading'),
                    ]),
                    Repeater::make('pageContent.loyalty.how_it_works_steps')
                        ->label('Steps')
                        ->schema([
                            TextInput::make('title')->required(),
                            TextInput::make('description')
                                ->required()
                                ->helperText('Use {{points_per_dollar}} for points value'),
                        ])
                        ->defaultItems(3)
                        ->maxItems(5),
                ])->compact(),
            ]);
    }
}
