<?php

use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('renders the customer 360 page with header stats and order history', function () {
    $customer = Customer::factory()->create([
        'name' => 'Maya Patel',
        'email' => 'maya@example.com',
        'phone' => '555-0100',
    ]);
    Order::factory()->for($customer)->count(2)->create();

    Livewire::test(ViewCustomer::class, ['record' => $customer->getRouteKey()])
        ->assertOk()
        ->assertSee('Maya Patel')
        ->assertSee('maya@example.com')
        ->assertSee('Order History');
});

test('shows the empty-state copy when the customer has no orders or notes', function () {
    $customer = Customer::factory()->create(['name' => 'Brand New']);

    Livewire::test(ViewCustomer::class, ['record' => $customer->getRouteKey()])
        ->assertOk()
        ->assertSee('No orders yet')
        ->assertSee('No notes yet');
});
