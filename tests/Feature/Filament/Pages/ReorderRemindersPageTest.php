<?php

use App\Filament\Pages\Operations\ReorderReminders;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('reorder reminders page can render', function () {
    livewire(ReorderReminders::class)
        ->assertOk();
});

test('customers are selected using their non-cancelled order history', function () {
    Date::setTestNow('2026-08-17 12:00:00');

    $lapsedCustomer = Customer::factory()->create();
    $activeCustomer = Customer::factory()->create();
    $cancelledOnlyCustomer = Customer::factory()->create();

    Order::factory()->for($lapsedCustomer)->delivered()->create([
        'delivery_date' => Date::now()->subDays(90),
        'total' => 25,
    ]);
    Order::factory()->for($lapsedCustomer)->delivered()->create([
        'delivery_date' => Date::now()->subDays(70),
        'total' => 35,
    ]);
    Order::factory()->for($lapsedCustomer)->cancelled()->create([
        'delivery_date' => Date::now()->subDays(5),
        'total' => 100,
    ]);
    Order::factory()->for($activeCustomer)->delivered()->create([
        'delivery_date' => Date::now()->subDays(10),
    ]);
    Order::factory()->for($cancelledOnlyCustomer)->cancelled()->create([
        'delivery_date' => Date::now()->subDays(90),
    ]);

    $page = new ReorderReminders;
    $customers = $page->getCustomers();
    $customer = $customers->sole();
    $lastOrderDate = $customer->last_order_date;

    throw_unless(is_string($lastOrderDate), UnexpectedValueException::class);

    expect($customers)->toHaveCount(1)
        ->and($customer->customer_email)->toBe($lapsedCustomer->email)
        ->and(Date::parse($lastOrderDate)->toDateString())->toBe(Date::now()->subDays(70)->toDateString())
        ->and($customer->total_orders)->toBe(2)
        ->and((float) $customer->total_spent)->toBe(6000.0)
        ->and($customer->days_since)->toBe(70);
});

test('customers lapse and count days against the bakery-local date', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    $recentCustomer = Customer::factory()->create();
    $lapsedCustomer = Customer::factory()->create();
    Order::factory()->for($recentCustomer)->delivered()->create(['delivery_date' => '2026-09-06']);
    Order::factory()->for($lapsedCustomer)->delivered()->create(['delivery_date' => '2026-09-04']);

    $page = new ReorderReminders;
    $page->threshold = 30;
    $customers = $page->getCustomers();

    expect($customers)->toHaveCount(1)
        ->and($customers->sole()->customer_email)->toBe($lapsedCustomer->email)
        ->and($customers->sole()->days_since)->toBe(31);
});
