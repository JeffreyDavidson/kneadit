<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\User;
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
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('order_number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
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
                                    ->options([
                                        'pending' => 'Pending',
                                        'confirmed' => 'Confirmed',
                                        'baking' => 'Baking',
                                        'ready' => 'Ready',
                                        'delivered' => 'Delivered',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->required(),

                                Select::make('payment_status')
                                    ->options([
                                        'unpaid' => 'Unpaid',
                                        'paid' => 'Paid',
                                        'cancelled' => 'Cancelled',
                                        'refunded' => 'Refunded',
                                    ])
                                    ->required(),
                            ]),

                        Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'paypal' => 'PayPal',
                                'other' => 'Other',
                            ])
                            ->required(),

                        Select::make('user_id')
                            ->label('Baker')
                            ->options(User::query()->pluck('name', 'id'))
                            ->required(),
                    ]),

                Section::make('Pricing')
                    ->components([
                        Grid::make(4)
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

                                TextInput::make('discount')
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
                    ->components([
                        Textarea::make('delivery_address')
                            ->rows(2),

                        Grid::make(2)
                            ->components([
                                DatePicker::make('requested_date'),

                                TimePicker::make('requested_time'),
                            ]),

                        Textarea::make('notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
