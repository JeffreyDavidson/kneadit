<?php

use App\Http\Requests\Storefront\StoreOrderRequest;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Operations\BusinessSchedule;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $category = Category::factory()->create(['name' => 'Bread', 'slug' => 'bread']);
    test()->product = Product::factory()->for($category)->create([
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'price' => 5.00,
        'is_active' => true,
    ]);
});

test('store order request requires essential fields', function () {
    $request = new StoreOrderRequest;

    foreach (['customer_name', 'customer_email', 'delivery_type', 'delivery_date', 'items'] as $field) {
        $data = validOrderData();
        unset($data[$field]);

        $validator = validator($data, $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has($field))->toBeTrue();
    }
});

test('store order request requires delivery address for delivery orders', function () {
    $request = new StoreOrderRequest;
    $data = array_merge(validOrderData(), [
        'delivery_type' => 'delivery',
        'delivery_address' => null,
    ]);

    $validator = validator($data, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('delivery_address'))->toBeTrue();
});

test('store order request rejects delivery date too soon', function () {
    $request = new StoreOrderRequest;
    $data = array_merge(validOrderData(), [
        'delivery_date' => now()->toDateString(),
    ]);

    $validator = validator($data, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('delivery_date'))->toBeTrue();
});

test('store order request rejects a date ruled out only by the order cutoff', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['leadTimeHours' => 48])));
    BusinessSchedule::factory()->open()->create(['day_of_week' => 1, 'order_cutoff_time' => '14:00']);
    Date::setTestNow('2026-10-05 15:00');

    $rules = (new StoreOrderRequest)->rules();

    expect(validator(array_merge(validOrderData(), ['delivery_date' => '2026-10-07']), $rules)->errors()->has('delivery_date'))->toBeTrue()
        ->and(validator(array_merge(validOrderData(), ['delivery_date' => '2026-10-08']), $rules)->errors()->has('delivery_date'))->toBeFalse();
});

function validOrderData(): array
{
    return [
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'delivery_type' => 'pickup',
        'delivery_date' => now()->addDays(3)->toDateString(),
        'items' => [
            ['product_id' => 1, 'quantity' => 2],
        ],
    ];
}
