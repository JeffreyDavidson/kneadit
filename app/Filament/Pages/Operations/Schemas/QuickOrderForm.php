<?php

namespace App\Filament\Pages\Operations\Schemas;

use App\DataTransferObjects\Settings\SettingValue;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\PaymentMethod;
use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Forms\Components\PhoneInput;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Services\Scheduling\BakeryClock;
use App\Services\Settings\TenantSettings;
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
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
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
                            ->required()
                            ->live(),
                    ]),

                    PhoneInput::make('customer_phone')
                        ->label('Phone')
                        ->live(),
                ])
                ->collapsible(),

            Section::make('Order Items')
                ->description(function (Get $get): string {
                    /** @var array<int, array{quantity: int, unit_price: float}> $items */
                    $items = $get('order_items') ?? [];
                    $totalItems = count($items);
                    $subtotal = collect($items)->sum(fn (array $item): float => $item['quantity'] * $item['unit_price']);

                    return $totalItems.' items · Subtotal: '.Number::currency($subtotal);
                })
                ->schema([
                    Repeater::make('order_items')
                        ->hiddenLabel()
                        ->schema([
                            Grid::make(['default' => 2, 'lg' => 12])->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->columnSpan(['default' => 2, 'lg' => 4])
                                    ->required()
                                    ->options(Product::query()
                                        ->active()
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn (Product $product): array => [
                                            $product->id => $product->name.' - '.($product->price?->formatted() ?? ''),
                                        ]))
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                        if ($state) {
                                            $product = Product::query()->find($state);
                                            if ($product) {
                                                $set('unit_price', $product->price?->dollars());
                                            }
                                        }
                                        $set('line_total', self::lineTotal($get));
                                    }),

                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->columnSpan(['default' => 1, 'lg' => 2])
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set('line_total', self::lineTotal($get))),

                                MoneyInput::make('unit_price')
                                    ->label('Price')
                                    ->columnSpan(['default' => 1, 'lg' => 3])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set('line_total', self::lineTotal($get))),

                                TextInput::make('line_total')
                                    ->label('Total')
                                    ->columnSpan(['default' => 2, 'lg' => 3])
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn (Get $get): string => self::lineTotal($get)),
                            ])->columnSpanFull(),

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
                            ->minDate(resolve(BakeryClock::class)->today()),

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
                            ->default(DeliveryType::Pickup),

                        TextInput::make('delivery_address')
                            ->label('Delivery Address')
                            ->visible(fn (Get $get): bool => self::isDelivery($get))
                            ->required(fn (Get $get): bool => self::isDelivery($get)),
                    ]),

                    Select::make('delivery_tier')
                        ->label('Delivery Distance')
                        ->options(fn (): array => self::deliveryTierOptions())
                        ->helperText(fn (): ?string => self::deliveryTierOptions() === []
                            ? 'No delivery fee tiers are set up, so delivery is free. Add them in Settings → Order Settings.'
                            : null)
                        ->visible(fn (Get $get): bool => self::isDelivery($get))
                        ->required(fn (Get $get): bool => self::isDelivery($get) && self::deliveryTierOptions() !== []),
                ])
                ->collapsible(),

            Section::make('Payment & Notes')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->required()
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::Cash),

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

    /**
     * The line's quantity times its price, as a plain amount (the field already has a "$" prefix).
     */
    private static function lineTotal(Get $get): string
    {
        $quantity = filter_var($get('quantity'), FILTER_VALIDATE_FLOAT);
        $price = filter_var($get('unit_price'), FILTER_VALIDATE_FLOAT);

        return number_format(
            (is_float($quantity) ? $quantity : 0.0) * (is_float($price) ? $price : 0.0),
            2,
        );
    }

    /**
     * Select state comes back from `$get()` as the enum, not its string value.
     */
    private static function isDelivery(Get $get): bool
    {
        return $get->enum('delivery_type', DeliveryType::class, isNullable: true) === DeliveryType::Delivery;
    }

    /**
     * The bakery's Delivery Fee Tiers, keyed by tier position as the storefront submits them.
     *
     * @return array<int, string>
     */
    private static function deliveryTierOptions(): array
    {
        return collect(resolve(TenantSettings::class)->orders->deliveryFeeTiers)
            ->map(fn (array $tier): string => sprintf(
                '%s (%s)',
                SettingValue::string($tier['description'] ?? null) ?: 'Delivery',
                Number::currency(SettingValue::float($tier['fee'] ?? null, 0.0)),
            ))
            ->all();
    }
}
