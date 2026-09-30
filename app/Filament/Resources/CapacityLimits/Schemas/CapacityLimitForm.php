<?php

namespace App\Filament\Resources\CapacityLimits\Schemas;

use App\Builders\Operations\CapacityLimitQueryBuilder;
use App\Enums\Staff\DayOfWeek;
use App\Models\Operations\CapacityLimit;
use Closure;
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
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Date;

class CapacityLimitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Capacity Limit')
                ->icon(Heroicon::OutlinedClock)
                ->description('Set order limits for a specific day or recurring weekday')
                ->columns(1)
                ->columnSpanFull()
                ->components([
                    Select::make('day_type')
                        ->label('Day')
                        ->options(DayOfWeek::options() + ['specific' => 'Specific Date'])
                        ->required()
                        ->live()
                        ->afterStateHydrated(function (Select $component, ?CapacityLimit $record): void {
                            if (! $record instanceof CapacityLimit) {
                                return;
                            }
                            if ($record->specific_date) {
                                $component->state('specific');
                            } else {
                                $component->state((string) $record->day_of_week);
                            }
                        })
                        ->dehydrated(false)
                        ->unique(CapacityLimit::class, 'day_of_week')
                        ->validationMessages(['unique' => 'A limit for this weekday already exists.'])
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            if ($state === 'specific') {
                                $set('day_of_week', null);
                            } else {
                                $set('day_of_week', $state);
                                $set('specific_date', null);
                            }
                        }),

                    DatePicker::make('specific_date')
                        ->label('Date')
                        ->visible(fn (Get $get): bool => $get('day_type') === 'specific')
                        ->required(fn (Get $get): bool => $get('day_type') === 'specific')
                        // Compare as dates: the stored value may carry a time part.
                        ->rule(fn (?CapacityLimit $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                            if (! is_string($value) || strtotime($value) === false) {
                                return;
                            }

                            $taken = CapacityLimit::query()
                                ->onSpecificDate(Date::parse($value))
                                ->when($record, fn (CapacityLimitQueryBuilder $query, CapacityLimit $record) => $query->whereKeyNot($record->getKey()))
                                ->exists();

                            if ($taken) {
                                $fail('A limit for this date already exists.');
                            }
                        })
                        ->native(false),

                    Hidden::make('day_of_week'),

                    TextInput::make('max_orders')
                        ->label('Max Orders')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->prefixIcon(Heroicon::OutlinedShoppingBag)
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
