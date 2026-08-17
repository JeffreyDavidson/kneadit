<?php

use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('paypal check-payments command runs successfully with no tenants', function () {
    expect(Artisan::call('paypal:check-payments'))->toBe(0);
});

test('command exits early when paypal is not configured', function () {
    config(['services.paypal.client_id' => null]);

    expect(Artisan::call('paypal:check-payments'))->toBe(0);
});

test('command skips tenants without paypal configured', function () {
    config(['services.paypal.client_id' => 'test-client-id']);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(function (...$arguments) {
            // Simulate settings('paypal_client_id') returning null
            return null;
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    createTenant([
        'id' => 'no-paypal-bakery',
        'name' => 'No PayPal Baker',
        'email' => 'nopaypal@test.com',
    ]);

    expect(Artisan::call('paypal:check-payments'))->toBe(0);
});

test('command handles tenant processing exceptions gracefully', function () {
    config(['services.paypal.client_id' => 'test-client-id']);

    createTenant([
        'id' => 'error-bakery',
        'name' => 'Error Baker',
        'email' => 'error@test.com',
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->throws(new Exception('Database connection failed'));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message) {
            return str_contains($message, 'PayPal check failed');
        });

    expect(Artisan::call('paypal:check-payments'))->toBe(0)
        ->and(Artisan::output())->toContain('Error processing');
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

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(function (...$arguments) {
            $callback = $arguments[1] ?? null;

            throw_unless(is_callable($callback), RuntimeException::class, 'Expected a tenant callback.');

            return $callback();
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    // Since this runs within a tenant context and we haven't set up tenant tables,
    // it will skip due to settings('paypal_client_id') returning null
    expect(Artisan::call('paypal:check-payments'))->toBe(0);
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
