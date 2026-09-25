<?php

declare(strict_types=1);

namespace App\Filament\Resources\CateringInquiries\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class CateringEventDetailsFields
{
    /** @return array<int, Field> */
    public static function make(bool $limitDateToTodayOrLater = false): array
    {
        $eventDate = DatePicker::make('event_date')->required();

        if ($limitDateToTodayOrLater) {
            $eventDate->minDate(now());
        }

        return [
            Select::make('event_type')
                ->options(function (TenantSettings $settings): array {
                    $types = $settings->catering->eventTypes;

                    return array_combine($types, $types);
                })
                ->required(),
            $eventDate,
            TextInput::make('guest_count')
                ->numeric()
                ->required()
                ->minValue(1),
            MoneyInput::make('budget')
                ->placeholder('Optional'),
            Textarea::make('details')
                ->required()
                ->columnSpanFull()
                ->rows(4)
                ->label('What they want'),
            Textarea::make('dietary_requirements')
                ->columnSpanFull()
                ->rows(2),
            Textarea::make('venue_address')
                ->columnSpanFull()
                ->rows(2),
        ];
    }
}
