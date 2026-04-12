<?php

use App\Enums\Customers\WaitlistStatus;
use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\StatsOverview;
use App\Models\Customers\Customer;
use App\Models\Customers\WaitlistEntry;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
    Cache::flush();
});

test('stats overview renders with order data', function () {
    $customer = Customer::factory()->create();

    Order::factory()->recycle($customer)->create([
        'delivery_date' => today(),
        'status' => OrderStatus::Pending,
    ]);

    Order::factory()->recycle($customer)->create([
        'delivery_date' => today(),
        'status' => OrderStatus::Confirmed,
    ]);

    Livewire::test(StatsOverview::class)
        ->assertOk();
});

test('stats overview renders with waitlist entries', function () {
    WaitlistEntry::factory()->create([
        'status' => WaitlistStatus::Waiting,
        'requested_date' => today(),
    ]);

    Livewire::test(StatsOverview::class)
        ->assertOk();
});

test('stats overview renders with no data', function () {
    Livewire::test(StatsOverview::class)
        ->assertOk();
});
