<?php

use App\Filament\Pages\Operations\CustomerDirectory;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('customer directory renders with customer data', function () {
    Customer::factory()->count(3)->create();

    Livewire::test(CustomerDirectory::class)
        ->assertOk()
        ->assertSee('Customer');
});

test('total_spent formats raw aggregate cents as dollars', function () {
    $customer = Customer::factory()->create(['name' => 'Alice']);
    Order::factory()->for($customer)->create(['total' => 60]);
    Order::factory()->for($customer)->create(['total' => 40]);

    $row = Livewire::test(CustomerDirectory::class)
        ->instance()
        ->getCustomers()
        ->firstWhere('name', 'Alice');

    expect($row['total_spent'])->toBe('$100.00');
});
