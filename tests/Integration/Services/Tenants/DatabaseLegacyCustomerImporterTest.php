<?php

use App\Contracts\Tenants\LegacyCustomerImporter;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => setUpTenantTest());

it('imports customers from legacy orders and returns their current ids', function () {
    $result = resolve(LegacyCustomerImporter::class)->import([
        [
            'customer_name' => 'Jane Baker',
            'customer_email' => 'Jane@Example.com',
            'customer_phone' => '5551234567',
            'delivery_address' => '123 Main Street',
            'delivery_zip' => '12345',
        ],
    ]);

    $customerId = (int) DB::table('customers')->where('email', 'jane@example.com')->value('id');

    expect($result)->toBe(['jane@example.com' => $customerId]);
    test()->assertDatabaseHas('customers', [
        'id' => $customerId,
        'email' => 'jane@example.com',
        'name' => 'Jane Baker',
        'phone' => '5551234567',
        'zip' => '12345',
    ]);
});

it('updates existing customers without duplicating them', function () {
    $importer = resolve(LegacyCustomerImporter::class);
    $orders = [['customer_name' => 'Jane Baker', 'customer_email' => 'jane@example.com']];

    $first = $importer->import($orders);
    $second = $importer->import($orders);

    expect($second)->toEqual($first);
    test()->assertDatabaseCount('customers', 1);
});
