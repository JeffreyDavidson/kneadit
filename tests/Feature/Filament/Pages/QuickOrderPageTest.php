<?php

use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\PaymentMethod;
use App\Filament\Pages\Operations\QuickOrder;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('quick order page can render', function () {
    livewire(QuickOrder::class)
        ->assertOk();
});

test('quick order delivery tier is only shown for delivery', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [['description' => 'Local', 'fee' => 5.00]],
    ])));

    livewire(QuickOrder::class)
        ->fillForm(['delivery_type' => DeliveryType::Pickup->value])
        ->assertSchemaComponentHidden('delivery_tier', 'form')
        ->fillForm(['delivery_type' => DeliveryType::Delivery->value])
        ->assertSchemaComponentVisible('delivery_tier', 'form');
});

test('quick order delivery tier is required for delivery', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [['description' => 'Local', 'fee' => 5.00]],
    ])));

    livewire(QuickOrder::class)
        ->fillForm(['delivery_type' => DeliveryType::Delivery->value])
        ->call('createOrder')
        ->assertHasFormErrors(['delivery_tier' => 'required']);
});

test('quick order delivery tier lists the bakery tiers', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [
            ['description' => 'Local', 'fee' => 5.00],
            ['description' => 'Far', 'fee' => 12.50],
        ],
    ])));

    livewire(QuickOrder::class)
        ->fillForm(['delivery_type' => DeliveryType::Delivery->value])
        ->assertFormFieldExists('delivery_tier', fn (Select $field): bool => array_keys($field->getOptions()) === [0, 1]
            && $field->getOptions()[1] === 'Far ($12.50)');
});

test('quick order date picker starts at the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    $form = livewire(QuickOrder::class)->instance()->form;
    $minDate = collect($form->getFlatComponents())
        ->first(fn (Component $component): bool => $component instanceof DatePicker && $component->getName() === 'delivery_date')
        ->getMinDate();

    expect(Date::parse($minDate)->toDateString())->toBe('2026-10-05');
});

test('quick order creates a pickup order paid in cash', function () {
    $product = Product::factory()->create(['price' => 10.00]);

    livewire(QuickOrder::class)
        ->fillForm([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'delivery_time' => '14:00',
            'delivery_type' => DeliveryType::Pickup,
            'payment_method' => PaymentMethod::Cash,
            'order_items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10.00],
            ],
        ])
        ->call('createOrder')
        ->assertHasNoFormErrors()
        ->assertNotified('Order Created Successfully!');

    $order = Order::query()->with('orderItems')->sole();

    expect($order->delivery_type)->toBe(DeliveryType::Pickup)
        ->and($order->payment_method)->toBe(PaymentMethod::Cash)
        ->and($order->customer->email)->toBe('jane@example.com')
        ->and($order->orderItems)->toHaveCount(1)
        ->and($order->orderItems->first()->product_id)->toBe($product->id)
        ->and($order->orderItems->first()->quantity)->toBe(2)
        ->and($order->subtotal->dollars())->toEqual(20.0)
        ->and($order->delivery_fee->dollars())->toEqual(0.0)
        ->and($order->total->dollars())->toEqual(20.0);
});

test('quick order creates a delivery order priced by the chosen tier', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [
            ['description' => 'Local', 'fee' => 5.00],
            ['description' => 'Far', 'fee' => 12.50],
        ],
    ])));
    $product = Product::factory()->create(['price' => 10.00]);

    livewire(QuickOrder::class)
        ->fillForm([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'delivery_time' => '14:00',
            'delivery_type' => DeliveryType::Delivery,
            'delivery_tier' => 1,
            'delivery_address' => '1 Main St',
            'payment_method' => PaymentMethod::Cash,
            'order_items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00],
            ],
        ])
        ->call('createOrder')
        ->assertHasNoFormErrors()
        ->assertNotified('Order Created Successfully!');

    $order = Order::query()->sole();

    expect($order->delivery_type)->toBe(DeliveryType::Delivery)
        ->and($order->delivery_address)->toBe('1 Main St')
        ->and($order->delivery_fee->dollars())->toEqual(12.5)
        ->and($order->total->dollars())->toEqual(22.5);
});

test('quick order requires a customer email', function () {
    $product = Product::factory()->create(['price' => 10.00]);

    livewire(QuickOrder::class)
        ->fillForm([
            'customer_name' => 'Jane Doe',
            'customer_email' => null,
            'delivery_date' => now()->addDays(3)->toDateString(),
            'delivery_time' => '14:00',
            'delivery_type' => DeliveryType::Pickup,
            'payment_method' => PaymentMethod::Cash,
            'order_items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00],
            ],
        ])
        ->call('createOrder')
        ->assertHasFormErrors(['customer_email' => 'required']);

    expect(Order::query()->count())->toBe(0)
        ->and(Customer::query()->count())->toBe(0);
});
