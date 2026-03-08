<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Components\TextInput;
use Filament\Components\Select;
use Filament\Components\DateTimePicker;
use Filament\Components\Toggle;
use Filament\Layouts\Grid;
use Filament\Layouts\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon Details')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->alphaNum()
                                    ->uppercase(),
                                    
                                Select::make('type')
                                    ->required()
                                    ->options([
                                        'percentage' => 'Percentage',
                                        'fixed' => 'Fixed Amount',
                                    ])
                                    ->reactive(),
                                    
                                TextInput::make('value')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix(fn ($get) => $get('type') === 'fixed' ? '$' : '')
                                    ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : ''),
                                    
                                TextInput::make('min_order_amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('$'),
                            ]),
                    ]),
                    
                Section::make('Usage Limits')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('max_uses')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Unlimited'),
                                    
                                TextInput::make('used_count')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled(),
                            ]),
                    ]),
                    
                Section::make('Validity Period')
                    ->components([
                        Grid::make(2)
                            ->components([
                                DateTimePicker::make('starts_at')
                                    ->placeholder('Effective immediately'),
                                    
                                DateTimePicker::make('expires_at')
                                    ->placeholder('Never expires'),
                            ]),
                    ]),
                    
                Section::make('Status')
                    ->components([
                        Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }
}