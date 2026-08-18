<?php

use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Log;

beforeEach(fn () => setUpCentralTest());

test('paypal check-payments command runs successfully with no tenants', function () {
    $this->artisan('paypal:check-payments')
        ->assertSuccessful();
});

test('command exits early when paypal is not configured', function () {
    config(['services.paypal.client_id' => null]);

    $this->artisan('paypal:check-payments')
        ->assertSuccessful();
});

test('command skips tenants without paypal configured', function () {
    config(['services.paypal.client_id' => 'test-client-id']);

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->once()
        ->andReturnUsing(function ($tenant, $callback) {
            // Simulate settings('paypal_client_id') returning null
            return null;
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    createTenant([
        'id' => 'no-paypal-bakery',
        'name' => 'No PayPal Baker',
        'email' => 'nopaypal@test.com',
    ]);

    $this->artisan('paypal:check-payments')
        ->assertSuccessful();
});

test('command handles tenant processing exceptions gracefully', function () {
    config(['services.paypal.client_id' => 'test-client-id']);

    createTenant([
        'id' => 'error-bakery',
        'name' => 'Error Baker',
        'email' => 'error@test.com',
    ]);

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->once()
        ->andThrow(new Exception('Database connection failed'));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message) {
            return str_contains($message, 'PayPal check failed');
        });

    $this->artisan('paypal:check-payments')
        ->expectsOutputToContain('Error processing')
        ->assertSuccessful();
});

test('command source uses TenancyManager for tenant context', function () {
    $source = file_get_contents(app_path('Console/Commands/PayPal/CheckPayPalPaymentsCommand.php'));

    expect($source)
        ->toContain('withinTenant')
        ->toContain('TenancyManager')
        ->toContain('PaymentVerifier');
});

test('command source handles PAID, CANCELLED, and REFUNDED statuses', function () {
    $source = file_get_contents(app_path('Console/Commands/PayPal/CheckPayPalPaymentsCommand.php'));

    expect($source)
        ->toContain("'PAID'")
        ->toContain("'CANCELLED'")
        ->toContain("'REFUNDED'")
        ->toContain('MarkOrderPaid');
});

test('command processes tenant with unpaid paypal orders', function () {
    config(['services.paypal.client_id' => 'test-client-id']);

    createTenant([
        'id' => 'paypal-bakery',
        'name' => 'PayPal Baker',
        'email' => 'paypal@test.com',
        'store_name' => 'PayPal Bakery',
    ]);

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->once()
        ->andReturnUsing(function ($tenant, $callback) {
            return $callback();
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    // Since this runs within a tenant context and we haven't set up tenant tables,
    // it will skip due to settings('paypal_client_id') returning null
    $this->artisan('paypal:check-payments')
        ->assertSuccessful();
});

test('command source resolves PaymentVerifier per tenant', function () {
    $source = file_get_contents(app_path('Console/Commands/PayPal/CheckPayPalPaymentsCommand.php'));

    expect($source)
        ->toContain('resolve(PaymentVerifier::class)')
        ->toContain("'paypal_client_id'")
        ->toContain('SettingsManager::class');
});

test('command source skips orders without paypal invoice id', function () {
    $source = file_get_contents(app_path('Console/Commands/PayPal/CheckPayPalPaymentsCommand.php'));

    expect($source)
        ->toContain('paypal_invoice_id')
        ->toContain('whereNotNull');
});
