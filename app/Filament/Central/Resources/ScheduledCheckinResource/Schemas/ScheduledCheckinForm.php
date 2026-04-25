<?php

namespace App\Filament\Central\Resources\ScheduledCheckinResource\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ScheduledCheckinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_active')
                    ->label('Active — send to new bakers')
                    ->helperText('When off, new bakers skip this step in the drip.')
                    ->default(true)
                    ->live()
                    ->columnSpanFull(),

                Section::make('Trigger')
                    ->description('When this email goes out')
                    ->icon(Heroicon::OutlinedClock)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->helperText('Internal label. Not visible to bakers.')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('days_after_signup')
                            ->label('Days After Signup')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->live(debounce: 400)
                            ->suffix('days')
                            ->helperText('Common choices: 1, 3, 7, 14, 30.'),
                    ]),

                Section::make('Email Content')
                    ->description('What the baker receives')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: 400)
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->required()
                            ->rows(8)
                            ->live(debounce: 400)
                            ->columnSpanFull(),
                        View::make('filament.central.partials.scheduled-checkin-preview')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
