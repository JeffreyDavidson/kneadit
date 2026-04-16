<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class GiftCardsTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Gift Cards')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.gift_cards.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.gift_cards.hero_subtitle')
                        ->label('Hero Subtitle'),
                ]),
                Textarea::make('pageContent.gift_cards.hero_title')
                    ->label('Hero Title')
                    ->helperText('Use line breaks for multi-line')
                    ->rows(2),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.gift_cards.preview_label')
                        ->label('Preview Label'),
                    TextInput::make('pageContent.gift_cards.amount_label')
                        ->label('Amount Label'),
                ]),
                TextInput::make('pageContent.gift_cards.balance_heading')
                    ->label('Balance Check Heading'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.gift_cards.details_eyebrow')
                        ->label('Details Eyebrow'),
                    TextInput::make('pageContent.gift_cards.details_heading')
                        ->label('Details Heading'),
                ]),
                TextInput::make('pageContent.gift_cards.recipient_label')
                    ->label('Recipient Section Label'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.gift_cards.success_heading')
                        ->label('Success Heading'),
                    TextInput::make('pageContent.gift_cards.success_description')
                        ->label('Success Description'),
                ]),
                Section::make('Buttons & Messages')->schema([
                    TextInput::make('pageContent.gift_cards.check_balance_button')
                        ->label('"Check Balance" Button')
                        ->placeholder('Check Balance'),
                    TextInput::make('pageContent.gift_cards.flash_purchased')
                        ->label('Purchase Success Message')
                        ->placeholder('Gift card purchased successfully.'),
                ])->compact(),
            ]);
    }
}
