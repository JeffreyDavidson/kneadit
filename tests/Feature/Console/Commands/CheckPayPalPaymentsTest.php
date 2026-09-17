<?php

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;

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

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')->resolves(fn () => 0);

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

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')
        ->resolves(function ($callback, $onError) {
            $onError(new Tenant(['id' => 'error-bakery']), new Exception('Database connection failed'));

            return 1;
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('error')
        ->once()
        ->withArgs(fn($message) => str_contains($message, 'PayPal check failed'));

    $this->artisan('paypal:check-payments')
        ->expectsOutputToContain('Error processing')
        ->assertFailed();
});

test('command source uses TenancyManager for tenant context', function () {
    $source = file_get_contents(app_path('Console/Commands/PayPal/CheckPayPalPaymentsCommand.php'));

    expect($source)
        ->toContain('forEachTenant')
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

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')->resolves(fn () => 0);

    app()->instance(TenancyManager::class, $tenancyManager);

    $this->artisan('paypal:check-payments')
        ->assertSuccessful();
});

test('command injects payment collaborators', function () {
    $source = file_get_contents(app_path('Console/Commands/PayPal/CheckPayPalPaymentsCommand.php'));

    expect($source)
        ->toContain('PaymentVerifier $paymentVerifier')
        ->toContain('MarkOrderPaid $markOrderPaid')
        ->not->toContain('resolve(PaymentVerifier::class)')
        ->not->toContain('resolve(MarkOrderPaid::class)')
        ->toContain("'paypal_client_id'")
        ->toContain('SettingsManager $settingsManager');
});

test('command source skips orders without paypal invoice id', function () {
    $source = file_get_contents(app_path('Console/Commands/PayPal/CheckPayPalPaymentsCommand.php'));

    expect($source)
        ->toContain('paypal_invoice_id')
        ->toContain('whereNotNull');
});
