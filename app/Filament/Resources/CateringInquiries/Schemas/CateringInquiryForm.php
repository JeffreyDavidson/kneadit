<?php

namespace App\Filament\Resources\CateringInquiries\Schemas;

use App\Enums\Customers\CateringInquiryStatus;
use App\Filament\Forms\Components\ContactFields;
use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                ->schema(CateringEventDetailsFields::make(limitDateToTodayOrLater: true)),

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
