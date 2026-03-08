<?php

namespace App\Filament\Resources\BlockedDates\Schemas;

use Filament\Components\DatePicker;
use Filament\Components\Select;
use Filament\Components\TextInput;
use Filament\Components\Toggle;
use Filament\Layouts\Grid;
use Filament\Layouts\Section;
use Filament\Schemas\Schema;

class BlockedDateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Block Date')
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
                                ->visible(fn ($get) => !$get('is_all_day')),

                            TextInput::make('close_time')
                                ->label('Close Time')
                                ->type('time')
                                ->visible(fn ($get) => !$get('is_all_day')),
                        ]),
                    ]),
            ]);
    }
}
