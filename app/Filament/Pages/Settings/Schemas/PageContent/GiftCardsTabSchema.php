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
                Section::make('Hero')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.gift_cards.hero_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.gift_cards.hero_subtitle')
                            ->label('Subtitle'),
                    ]),
                    Textarea::make('pageContent.gift_cards.hero_title')
                        ->label('Title')
                        ->helperText('Use line breaks for multi-line')
                        ->rows(2),
                ])->compact(),
                Section::make('Card Preview')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.gift_cards.preview_label')
                            ->label('Preview Label'),
                        TextInput::make('pageContent.gift_cards.amount_label')
                            ->label('Amount Label'),
                    ]),
                    TextInput::make('pageContent.gift_cards.balance_heading')
                        ->label('Balance Check Heading'),
                ])->compact(),
                Section::make('Details')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.gift_cards.details_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.gift_cards.details_heading')
                            ->label('Heading'),
                    ]),
                    TextInput::make('pageContent.gift_cards.recipient_label')
                        ->label('Recipient Label'),
                ])->compact(),
                Section::make('Success State')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.gift_cards.success_heading')
                            ->label('Heading'),
                        TextInput::make('pageContent.gift_cards.success_description')
                            ->label('Description'),
                    ]),
                ])->compact(),
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
