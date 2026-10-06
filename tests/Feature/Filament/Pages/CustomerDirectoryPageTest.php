<?php

use App\Enums\Orders\PaymentStatus;
use App\Filament\Pages\Operations\CustomerDirectory;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Queries\Customers\CustomerDirectoryStatsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('customer directory renders with customer data', function () {
    Customer::factory()->count(3)->create();

    livewire(CustomerDirectory::class)
        ->assertOk()
        ->assertSee('Customer');
});

test('total_spent formats raw aggregate cents as dollars', function () {
    $customer = Customer::factory()->create(['name' => 'Alice']);
    Order::factory()->for($customer)->paid()->create(['total' => 60]);
    Order::factory()->for($customer)->paid()->create(['total' => 40]);

    $row = livewire(CustomerDirectory::class)
        ->instance()
        ->getCustomers()
        ->firstWhere('name', 'Alice');

    expect($row['total_spent'])->toBe('$100.00');
});

test('the row, the detail panel and the stats card count only revenue orders', function () {
    $customer = Customer::factory()->create(['name' => 'Alice']);
    Order::factory()->for($customer)->paid()->create(['total' => 100, 'delivery_date' => '2026-09-10', 'created_at' => '2026-09-01 12:00:00']);
    Order::factory()->for($customer)->create(['total' => 70, 'payment_status' => PaymentStatus::Refunded, 'delivery_date' => '2026-09-10', 'created_at' => '2026-09-02 12:00:00']);
    Order::factory()->for($customer)->paid()->cancelled()->create(['total' => 30, 'delivery_date' => '2026-09-10', 'created_at' => '2026-09-03 12:00:00']);

    $page = livewire(CustomerDirectory::class)->instance();
    $row = $page->getCustomers()->firstWhere('name', 'Alice');
    $detail = $page->getCustomerDetails($customer->id);
    $stats = CustomerDirectoryStatsQuery::get();

    expect($row['total_spent'])->toBe('$100.00')
        ->and($stats['top_customer_value'])->toBe('$100.00')
        ->and($stats['avg_lifetime_value'])->toBe('$100.00')
        ->and($detail['stats']['total_spent'])->toBe(100.0)
        ->and($detail['stats']['avg_order_value'])->toBe(100.0);
});

test('the directory shows money that is already formatted without a second dollar sign', function () {
    $customer = Customer::factory()->create(['name' => 'Alice']);
    Order::factory()->for($customer)->paid()->create(['total' => 100]);

    livewire(CustomerDirectory::class)
        ->assertSee('$100.00')
        ->assertDontSee('$$')
        ->assertDontSee("'$' + order.total", false);
});
