<?php

namespace App\Filament\Pages\Operations\Schemas;

use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\PaymentMethod;
use App\Filament\Forms\Components\MoneyInput;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Number;

class QuickOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::getComponents());
    }

    /** @return array<int, Component> */
    public static function getComponents(): array
    {
        return [
            Section::make('Customer Information')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('customer_search')
                            ->label('Search Customer')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => Customer::query()
                                ->whereLike('name', "%{$search}%")
                                ->orWhereLike('email', "%{$search}%")
                                ->orWhereLike('phone', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Customer $customer): array => [
                                    $customer->id => "{$customer->name} - {$customer->email}",
                                ])->all())
                            ->getOptionLabelUsing(fn (string $value): ?string => Customer::query()->find($value)?->name)
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $customer = Customer::query()->find($state);
                                    if ($customer) {
                                        $set('customer_name', $customer->name);
                                        $set('customer_email', $customer->email);
                                        $set('customer_phone', $customer->phone);
                                    }
                                }
                            }),

                        TextInput::make('customer_name')
                            ->label('Name')
                            ->required()
                            ->live(),

                        TextInput::make('customer_email')
                            ->label('Email')
                            ->email()
                            ->live(),
                    ]),

                    TextInput::make('customer_phone')
                        ->label('Phone')
                        ->tel()
                        ->live(),
                ])
                ->collapsible(),

            Section::make('Order Items')
                ->description(function (Get $get): string {
                    /** @var array<int, array{quantity: int, unit_price: float}> $items */
                    $items = $get('order_items') ?? [];
                    $totalItems = count($items);
                    $subtotal = collect($items)->sum(fn (array $item) => $item['quantity'] * $item['unit_price']);

                    return $totalItems . ' items · Subtotal: $' . Number::currency($subtotal);
                })
                ->schema([
                    Repeater::make('order_items')
                        ->hiddenLabel()
                        ->schema([
                            Grid::make(4)->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->required()
                                    ->options(Product::query()
                                        ->active()
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn (Product $product): array => [
                                            $product->id => $product->name . ' - ' . ($product->price?->formatted() ?? ''),
                                        ]))
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if ($state) {
                                            $product = Product::query()->find($state);
                                            if ($product) {
                                                $set('unit_price', $product->price?->dollars());
                                            }
                                        }
                                    }),

                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live(),

                                MoneyInput::make('unit_price')
                                    ->label('Price')
                                    ->required()
                                    ->live(),

                                TextInput::make('line_total')
                                    ->label('Total')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function (Get $get) {
                                        $quantity = (float) $get('quantity');
                                        $price = (float) $get('unit_price');

                                        return Number::currency($quantity * $price);
                                    }),
                            ]),

                            Textarea::make('special_instructions')
                                ->label('Special Instructions')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel('Add Item')
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->cloneable(),
                ])
                ->collapsible(),

            Section::make('Order Details')
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('delivery_date')
                            ->label('Requested Date')
                            ->required()
                            ->minDate(today()),

                        TimePicker::make('delivery_time')
                            ->label('Requested Time')
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('delivery_type')
                            ->label('Delivery Type')
                            ->required()
                            ->options(DeliveryType::class)
                            ->live()
                            ->default(DeliveryType::Pickup->value),

                        TextInput::make('delivery_address')
                            ->label('Delivery Address')
                            ->visible(fn (Get $get): bool => $get('delivery_type') === DeliveryType::Delivery->value)
                            ->required(fn (Get $get): bool => $get('delivery_type') === DeliveryType::Delivery->value),
                    ]),
                ])
                ->collapsible(),

            Section::make('Payment & Notes')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->required()
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::Cash->value),

                        Textarea::make('notes')
                            ->label('Order Notes')
                            ->rows(3),
                    ]),
                ])
                ->collapsible(),

            Actions::make([
                Action::make('create_order')
                    ->label('Create Order')
                    ->action('createOrder')
                    ->color('primary')
                    ->size('lg')
                    ->icon(Heroicon::OutlinedShoppingBag),
            ])
                ->alignEnd(),
        ];
    }
}
