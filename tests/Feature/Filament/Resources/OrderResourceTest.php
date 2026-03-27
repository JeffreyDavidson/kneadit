<?php

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('can render the orders list page', function () {
    Livewire::test(ListOrders::class)
        ->assertOk();
});

test('can list orders in the table', function () {
    $orders = Order::factory()->count(3)->create();

    Livewire::test(ListOrders::class)
        ->assertCanSeeTableRecords($orders);
});

test('can render table columns', function (string $column) {
    Order::factory()->create();

    Livewire::test(ListOrders::class)
        ->assertCanRenderTableColumn($column);
})->with(['order_number', 'customer.name', 'status', 'payment_status', 'total', 'delivery_date']);
