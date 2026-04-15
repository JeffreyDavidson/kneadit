<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Staff\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Details')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('order_number')
                                    ->required()
                                    ->unique()
                                    ->maxLength(255),

                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->options(Customer::query()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                            ]),

                        Grid::make(2)
                            ->components([
                                Select::make('status')
                                    ->options(OrderStatus::class)
                                    ->required(),

                                Select::make('payment_status')
                                    ->options(PaymentStatus::class)
                                    ->required(),
                            ]),

                        Select::make('payment_method')
                            ->options(PaymentMethod::class)
                            ->required(),

                        Select::make('user_id')
                            ->label('Baker')
                            ->options(User::query()->pluck('name', 'id'))
                            ->required(),
                    ]),

                Section::make('Pricing')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(5)
                            ->components([
                                TextInput::make('subtotal')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),

                                TextInput::make('delivery_fee')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->default(0),

                                TextInput::make('discount_amount')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->default(0),

                                TextInput::make('gift_card_amount')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->default(0),

                                TextInput::make('total')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),
                            ]),
                    ]),

                Section::make('Delivery & Timing')
                    ->columnSpanFull()
                    ->components([
                        Textarea::make('delivery_address')
                            ->rows(2),

                        Grid::make(2)
                            ->components([
                                DatePicker::make('delivery_date'),

                                TimePicker::make('delivery_time'),
                            ]),

                        Textarea::make('notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
