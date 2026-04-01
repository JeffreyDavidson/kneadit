<?php

namespace App\Filament\Resources\HolidayResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Holiday Details')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->description('Plan for upcoming holiday orders')
                ->columns(1)
                ->columnSpanFull()
                ->components([
                    TextInput::make('name')
                        ->label('Holiday Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Thanksgiving, Valentine\'s Day'),

                    DatePicker::make('date')
                        ->label('Holiday Date')
                        ->required()
                        ->native(false),

                    DatePicker::make('order_deadline')
                        ->label('Order Deadline')
                        ->required()
                        ->native(false)
                        ->helperText('Last day customers can place orders'),

                    DatePicker::make('prep_start')
                        ->label('Prep Start Date')
                        ->native(false)
                        ->helperText('When to begin prepping for this holiday'),

                    TextInput::make('max_orders')
                        ->label('Max Orders')
                        ->numeric()
                        ->minValue(1)
                        ->prefixIcon(Heroicon::OutlinedShoppingBag)
                        ->helperText('Leave blank for no limit'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('Special menu items, instructions, etc.')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
