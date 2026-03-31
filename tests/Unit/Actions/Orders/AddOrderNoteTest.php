<?php

use App\Actions\Orders\AddOrderNote;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it appends a timestamped note to the order', function () {
    $order = Order::factory()->create(['notes' => null]);

    $action = new AddOrderNote;
    $action($order, 'Customer called about pickup time');

    $order->refresh();

    expect($order->notes)->toContain('Customer called about pickup time')
        ->and($order->notes)->toMatch('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/');
});
