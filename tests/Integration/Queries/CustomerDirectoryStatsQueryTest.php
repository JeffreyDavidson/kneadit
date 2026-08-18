<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Queries\Customers\CustomerDirectoryStatsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('returns customer directory statistics', function () {
    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create(['name' => 'Jane Baker']);

    Order::factory()
        ->for($customer)
        ->recycle($user)
        ->delivered()
        ->create(['total' => 100.00]);

    $stats = CustomerDirectoryStatsQuery::get();

    expect($stats)
        ->toHaveKeys(['total_customers', 'avg_lifetime_value', 'at_risk_count', 'top_customer_name', 'top_customer_value'])
        ->and($stats['total_customers'])->toBe(1)
        ->and($stats['top_customer_name'])->toBe('Jane Baker');
});
