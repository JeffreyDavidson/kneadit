<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Models\Customers\Customer;
use App\Pipes\Orders\OrderPipelineData;
use App\Pipes\Orders\ResolveCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function resolveCustomerPayload(string $email): OrderPipelineData
{
    return new OrderPipelineData(new CreateOrderData(
        customerName: 'Bob',
        customerEmail: $email,
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => 1, 'quantity' => 1]],
    ));
}

test('reuses the existing customer when the checkout email differs only by case', function (string $submitted) {
    $existing = Customer::factory()->create(['email' => 'bob@example.com']);

    $result = new ResolveCustomer()->handle(resolveCustomerPayload($submitted), fn ($payload) => $payload);

    expect($result->customer->is($existing))->toBeTrue()
        ->and(Customer::query()->count())->toBe(1);
})->with([
    'mixed case' => 'Bob@Example.com',
    'upper case' => 'BOB@EXAMPLE.COM',
    'padded' => '  bob@example.com ',
]);

test('creates new customers with a lowercased email', function () {
    $result = new ResolveCustomer()->handle(resolveCustomerPayload('New.Person@Example.com'), fn ($payload) => $payload);

    expect($result->customer->email)->toBe('new.person@example.com')
        ->and(Customer::query()->count())->toBe(1);
});
