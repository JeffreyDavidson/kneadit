<?php

namespace App\Filament\Pages;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Traits\RequiresRole;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class QuickOrder extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected string $view = 'filament-panels::pages.simple';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-plus-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?string $title = 'Quick Order';

    protected static ?string $navigationLabel = 'Quick Order';

    protected static ?int $navigationSort = 7;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('form')
                ->schema($this->getFormSchema()),
        ]);
    }

    protected function getForms(): array
    {
        return [
            'form' => Form::make($this, [
                EmbeddedSchema::make('form')
                    ->schema($this->getFormSchema()),
            ])
                ->statePath('data')
                ->model(Order::class),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Customer Information')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('customer_search')
                            ->label('Search Customer')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => Customer::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Customer $customer): array => [
                                    $customer->id => "{$customer->name} - {$customer->email}",
                                ])->toArray())
                            ->getOptionLabelUsing(fn ($value): ?string => Customer::find($value)?->name)
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $customer = Customer::find($state);
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
                    $items = $get('order_items') ?? [];
                    $totalItems = count($items);
                    $subtotal = collect($items)->sum(fn ($item) => ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0));

                    return $totalItems.' items · Subtotal: $'.number_format($subtotal, 2);
                })
                ->schema([
                    Repeater::make('order_items')
                        ->label('')
                        ->schema([
                            Grid::make(4)->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->required()
                                    ->options(Product::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn (Product $product): array => [
                                            $product->id => $product->name.' - $'.number_format($product->price, 2),
                                        ]))
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('unit_price', $product->price);
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

                                TextInput::make('unit_price')
                                    ->label('Price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->live(),

                                TextInput::make('line_total')
                                    ->label('Total')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function (Get $get) {
                                        $quantity = (float) $get('quantity');
                                        $price = (float) $get('unit_price');

                                        return number_format($quantity * $price, 2);
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

    public function createOrder(): void
    {
        $data = $this->form->getState();

        try {
            DB::transaction(function () use ($data) {
                // Create or find customer
                $customer = null;

                if (! empty($data['customer_email'])) {
                    $customer = Customer::where('email', $data['customer_email'])->first();
                }

                if (! $customer) {
                    $customer = Customer::create([
                        'name' => $data['customer_name'],
                        'email' => $data['customer_email'] ?? null,
                        'phone' => $data['customer_phone'] ?? null,
                    ]);
                }

                // Calculate totals
                $orderItems = $data['order_items'] ?? [];
                $subtotal = collect($orderItems)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
                $deliveryFee = ($data['delivery_type'] === DeliveryType::Delivery->value) ? 5.00 : 0.00;
                $total = $subtotal + $deliveryFee;

                // Create order
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'status' => OrderStatus::Pending,
                    'payment_status' => PaymentStatus::Unpaid,
                    'payment_method' => $data['payment_method'],
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'total' => $total,
                    'delivery_address' => $data['delivery_type'] === DeliveryType::Delivery->value ? $data['delivery_address'] : null,
                    'delivery_date' => $data['delivery_date'],
                    'delivery_time' => $data['delivery_time'],
                    'notes' => $data['notes'] ?? null,
                    'user_id' => auth()->id(),
                ]);

                // Create order items
                foreach ($orderItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'special_instructions' => $item['special_instructions'] ?? null,
                    ]);
                }

                Notification::make()
                    ->title('Order Created Successfully!')
                    ->body("Order #{$order->order_number} has been created for {$customer->name}.")
                    ->success()
                    ->send();

                // Clear the form
                $this->form->fill();
            });

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Creating Order')
                ->body('There was an error creating the order. Please try again.')
                ->danger()
                ->send();
        }
    }
}
