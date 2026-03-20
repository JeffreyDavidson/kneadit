<?php

namespace App\Filament\Resources\BlockedDates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class BlockedDateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Block Date')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)->components([
                            DatePicker::make('date')
                                ->required()
                                ->native(false),

                            Select::make('reason')
                                ->options([
                                    'Vacation' => 'Vacation',
                                    'Holiday' => 'Holiday',
                                    'Maintenance' => 'Maintenance',
                                    'Personal' => 'Personal',
                                    'Other' => 'Other',
                                ])
                                ->placeholder('Select reason...'),
                        ]),

                        Toggle::make('is_all_day')
                            ->label('All Day')
                            ->default(true)
                            ->reactive(),

                        Grid::make(2)->components([
                            TextInput::make('open_time')
                                ->label('Open Time')
                                ->type('time')
                                ->visible(fn (Get $get) => ! $get('is_all_day')),

                            TextInput::make('close_time')
                                ->label('Close Time')
                                ->type('time')
                                ->visible(fn (Get $get) => ! $get('is_all_day')),
                        ]),
                    ]),
            ]);
    }
}
