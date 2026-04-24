<?php

namespace App\Filament\Resources\CateringInquiries\Schemas;

use App\Enums\Customers\CateringInquiryStatus;
use App\Filament\Forms\Components\ContactFields;
use App\Filament\Forms\Components\MoneyInput;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CateringInquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer Information')
                ->columnSpanFull()
                ->columns(3)
                ->schema(ContactFields::nameEmailPhone()),

            Section::make('Event Details')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('event_type')
                        ->options(function () {
                            $types = resolve(TenantSettings::class)->catering->eventTypes;

                            return array_combine($types, $types);
                        })
                        ->required(),
                    DatePicker::make('event_date')
                        ->required()
                        ->minDate(now()),
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
                ]),

            Section::make('Status & Quote')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(CateringInquiryStatus::class)
                        ->default(CateringInquiryStatus::Inquiry)
                        ->required(),
                    MoneyInput::make('quoted_amount')
                        ->placeholder('Enter quote amount'),
                    Textarea::make('notes')
                        ->columnSpanFull()
                        ->rows(3)
                        ->label('Internal Notes'),
                ]),
        ]);
    }
}
