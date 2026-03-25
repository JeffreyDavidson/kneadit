<?php

namespace App\Filament\Resources\CapacityLimits\Schemas;

use App\Models\CapacityLimit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CapacityLimitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Capacity Limit')
                ->icon('heroicon-o-clock')
                ->description('Set order limits for a specific day or recurring weekday')
                ->columns(1)
                ->columnSpanFull()
                ->components([
                    Select::make('day_type')
                        ->label('Day')
                        ->options([
                            '0' => 'Monday',
                            '1' => 'Tuesday',
                            '2' => 'Wednesday',
                            '3' => 'Thursday',
                            '4' => 'Friday',
                            '5' => 'Saturday',
                            '6' => 'Sunday',
                            'specific' => 'Specific Date',
                        ])
                        ->required()
                        ->live()
                        ->afterStateHydrated(function (mixed $component, ?CapacityLimit $record) {
                            if (! $record) {
                                return;
                            }
                            if ($record->specific_date) {
                                $component->state('specific');
                            } else {
                                $component->state((string) $record->day_of_week);
                            }
                        })
                        ->dehydrated(false)
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if ($state === 'specific') {
                                $set('day_of_week', null);
                            } else {
                                $set('day_of_week', (int) $state);
                                $set('specific_date', null);
                            }
                        }),

                    DatePicker::make('specific_date')
                        ->label('Date')
                        ->visible(fn (Get $get) => $get('day_type') === 'specific')
                        ->required(fn (Get $get) => $get('day_type') === 'specific')
                        ->native(false),

                    Hidden::make('day_of_week'),

                    TextInput::make('max_orders')
                        ->label('Max Orders')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->prefixIcon('heroicon-o-shopping-bag')
                        ->helperText('0 = unlimited (unless blocked)'),

                    Toggle::make('is_blocked')
                        ->label('Block Day Entirely')
                        ->helperText('No orders allowed at all on this day'),

                    Textarea::make('notes')
                        ->rows(2)
                        ->maxLength(500)
                        ->placeholder('Optional notes (e.g. "Holiday closure")')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
