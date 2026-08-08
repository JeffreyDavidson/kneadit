<?php

use App\Events\Platform\WeeklyDigestRequested;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('digest:weekly command runs successfully with no tenants', function () {
    $this->artisan('digest:weekly')
        ->assertSuccessful();
});

test('digest:weekly skips tenants with digest disabled', function () {
    Event::fake([WeeklyDigestRequested::class]);

    createTenant([
        'id' => 'digest-disabled',
        'name' => 'Disabled Baker',
        'email' => 'disabled@test.com',
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(function ($tenant, $callback) {
            // Simulate settings returning '0' for digest
            return null;
        });

    app()->instance(TenancyManager::class, $tenancyManager);

    $this->artisan('digest:weekly')
        ->assertSuccessful();

    Event::assertNotDispatched(WeeklyDigestRequested::class);
});

test('digest:weekly returns failure when tenant processing fails', function () {
    createTenant([
        'id' => 'failing-bakery',
        'name' => 'Failing Baker',
        'email' => 'failing@test.com',
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->throws(new RuntimeException('Tenant DB unavailable'));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('warning')
        ->once()
        ->withArgs(function ($message) {
            return str_contains($message, 'Weekly digest processing failed');
        });

    $this->artisan('digest:weekly')
        ->expectsOutputToContain('Failed')
        ->assertFailed();
});

test('digest:weekly command source resolves WeeklyDigestDataCollector', function () {
    $source = file_get_contents(app_path('Console/Commands/Platform/SendWeeklyDigestCommand.php'));

    expect($source)
        ->toContain('WeeklyDigestDataCollector')
        ->toContain('WeeklyDigestRequested')
        ->toContain('withinTenant');
});

test('digest:weekly command source checks for owner users first', function () {
    $source = file_get_contents(app_path('Console/Commands/Platform/SendWeeklyDigestCommand.php'));

    expect($source)
        ->toContain('owners()')
        ->toContain('limit(1)');
});
