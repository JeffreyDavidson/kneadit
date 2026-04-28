<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Filament\Forms\Components\MoneyInput;
use App\Models\Customers\Customer;
use App\Models\Staff\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::detailsSection(),
            self::pricingSection(),
            self::deliverySection(),
        ]);
    }

    private static function detailsSection(): Section
    {
        return Section::make('Order Details')
            ->columnSpanFull()
            ->components([
                Grid::make(2)->components([
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

                Grid::make(2)->components([
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
            ]);
    }

    private static function pricingSection(): Section
    {
        // Total auto-computes from line items. Each contributing field is
        // ->live() and re-runs this callback after edit, so the disabled
        // Total field stays in sync as the user types.
        $recalculateTotal = function (Get $get, Set $set): void {
            $total = (float) $get('subtotal')
                + (float) $get('delivery_fee')
                - (float) $get('discount_amount')
                - (float) $get('gift_card_amount')
                + (float) $get('tip_amount');
            $set('total', max(0, round($total, 2)));
        };

        return Section::make('Pricing')
            ->columnSpanFull()
            ->components([
                // Line items + adjustments laid out 2-col so labels stay on
                // one line in the slide-over.
                Grid::make(2)->components([
                    MoneyInput::make('subtotal')
                        ->required()
                        ->default(0)
                        ->live(debounce: 300)
                        ->afterStateUpdated($recalculateTotal),
                    MoneyInput::make('delivery_fee')
                        ->default(0)
                        ->live(debounce: 300)
                        ->afterStateUpdated($recalculateTotal),
                    MoneyInput::make('discount_amount')
                        ->default(0)
                        ->live(debounce: 300)
                        ->afterStateUpdated($recalculateTotal),
                    MoneyInput::make('gift_card_amount')
                        ->default(0)
                        ->live(debounce: 300)
                        ->afterStateUpdated($recalculateTotal),
                ]),

                // Tip on its own row (5th of 5 line items, no pair).
                MoneyInput::make('tip_amount')
                    ->default(0)
                    ->columnSpanFull()
                    ->live(debounce: 300)
                    ->afterStateUpdated($recalculateTotal),

                // Computed result — disabled prevents user edits but
                // dehydrated() still submits the value with the form.
                MoneyInput::make('total')
                    ->label('Total')
                    ->required()
                    ->default(0)
                    ->disabled()
                    ->dehydrated()
                    ->columnSpanFull(),
            ]);
    }

    private static function deliverySection(): Section
    {
        return Section::make('Delivery & Timing')
            ->columnSpanFull()
            ->components([
                Textarea::make('delivery_address')->rows(2),

                Grid::make(2)->components([
                    DatePicker::make('delivery_date'),
                    TimePicker::make('delivery_time'),
                ]),

                Textarea::make('notes')->rows(3),
            ]);
    }
}
