<?php

namespace App\Filament\Pages;

use App\Actions\Orders\CreateQuickOrder;
use App\Enums\DeliveryType;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class QuickOrder extends Page
{
    protected string $view = 'filament-panels::pages.page';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return true;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?string $title = 'Quick Order';

    protected static ?string $navigationLabel = 'Quick Order';

    protected static ?int $navigationSort = 7;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getFormSchema())
            ->statePath('data')
            ->model(Order::class);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make($this->getFormSchema())
                ->livewireSubmitHandler('submit'),
        ]);
    }

    /** @return array<int, Component> */
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
                                            $product->id => $product->name . ' - ' . Number::currency($product->price),
                                        ]))
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if ($state) {
                                            $product = Product::query()->find($state);
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

    public function createOrder(): void
    {
        $data = $this->form->getState();

        try {
            $order = resolve(CreateQuickOrder::class)($data);

            Notification::make()
                ->title('Order Created Successfully!')
                ->body("Order #{$order->order_number} has been created.")
                ->success()
                ->send();

            $this->form->fill();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Creating Order')
                ->body('There was an error creating the order. Please try again.')
                ->danger()
                ->send();
        }
    }
}
