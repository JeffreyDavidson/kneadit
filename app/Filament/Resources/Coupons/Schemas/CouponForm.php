<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\Financial\CouponType;
use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Forms\Components\PercentageInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon Details')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique()
                                    ->alphaNum()
                                    ->formatStateUsing(fn (?string $state): ?string => $state ? Str::upper($state) : $state)
                                    ->dehydrateStateUsing(fn (?string $state): ?string => $state ? Str::upper($state) : $state),

                                Select::make('type')
                                    ->required()
                                    ->options(CouponType::class)
                                    ->live(),

                                MoneyInput::make('fixed_amount')
                                    ->label('Discount Amount')
                                    ->helperText('Use for fixed-amount discounts')
                                    ->required(fn (Get $get) => $get('type') === CouponType::Fixed->value),

                                PercentageInput::make('percentage')
                                    ->label('Discount Percentage')
                                    ->helperText('Use for percentage discounts')
                                    ->required(fn (Get $get) => $get('type') === CouponType::Percentage->value),

                                MoneyInput::make('min_order_amount')
                                    ->minValue(0),
                            ]),
                    ]),

                Section::make('Usage Limits')
                    ->columnSpanFull()
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
                    ->columnSpanFull()
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
                    ->columnSpanFull()
                    ->components([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
