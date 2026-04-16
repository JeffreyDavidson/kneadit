<?php

namespace App\Filament\Resources\CateringInquiries\Schemas;

use App\Enums\Customers\CateringInquiryStatus;
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
                ->schema([
                    TextInput::make('customer_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('customer_email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('customer_phone')
                        ->tel()
                        ->maxLength(255),
                ]),

            Section::make('Event Details')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('event_type')
                        ->options([
                            'wedding' => '💒 Wedding',
                            'corporate' => '🏢 Corporate',
                            'birthday' => '🎂 Birthday',
                            'holiday' => '🎄 Holiday',
                            'other' => '🎉 Other',
                        ])
                        ->required(),
                    DatePicker::make('event_date')
                        ->required()
                        ->minDate(now()),
                    TextInput::make('guest_count')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    TextInput::make('budget')
                        ->numeric()
                        ->prefix('$')
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
                    TextInput::make('quoted_amount')
                        ->numeric()
                        ->prefix('$')
                        ->placeholder('Enter quote amount'),
                    Textarea::make('notes')
                        ->columnSpanFull()
                        ->rows(3)
                        ->label('Internal Notes'),
                ]),
        ]);
    }
}
