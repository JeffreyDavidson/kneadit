<?php

use App\Contracts\Engagement\CustomerEngagement;
use App\Contracts\Engagement\EngagementRecipient;
use App\Models\Customers\Customer;
use App\Services\Engagement\EngagementDispatcher;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Command;

function makeFakeTenantSettings(): App\Services\Settings\TenantSettings
{
    return makeTenantSettings();
}

test('dispatches engagement to recipients across tenants', function () {
    $customer = Mockery::mock(Customer::class)->shouldIgnoreMissing();
    $customer->name = 'Jane Doe';
    $customer->email = 'jane@example.com';

    $recipient = new EngagementRecipient(
        email: 'jane@example.com',
        name: 'Jane Doe',
        model: $customer,
    );

    $settings = makeFakeTenantSettings();

    $engagement = Mockery::mock(CustomerEngagement::class);
    $engagement->shouldReceive('isEnabled')->with($settings)->andReturnTrue();
    $engagement->shouldReceive('findRecipients')->with($settings)->andReturn(collect([$recipient]));
    $engagement->shouldReceive('dispatchForRecipient')->with($recipient, $settings)->once();

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('forEachTenant')
        ->once()
        ->andReturnUsing(function (callable $callback, ?callable $onError) use ($settings) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'test-bakery';

            app()->instance(TenantSettings::class, $settings);

            $callback($tenant, $settings);

            return 0;
        });

    $output = Mockery::mock(Command::class);
    $output->shouldReceive('info')->andReturnSelf();
    $output->shouldReceive('error')->andReturnSelf();

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(0);
});

test('skips disabled engagements', function () {
    $settings = makeFakeTenantSettings();

    $engagement = Mockery::mock(CustomerEngagement::class);
    $engagement->shouldReceive('isEnabled')->with($settings)->andReturnFalse();
    $engagement->shouldNotReceive('findRecipients');

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('forEachTenant')
        ->once()
        ->andReturnUsing(function (callable $callback) use ($settings) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'test-bakery';

            $callback($tenant, $settings);

            return 0;
        });

    $output = Mockery::mock(Command::class);
    $output->shouldReceive('info')->andReturnSelf();

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(0);
});

test('skips when no recipients found', function () {
    $settings = makeFakeTenantSettings();

    $engagement = Mockery::mock(CustomerEngagement::class);
    $engagement->shouldReceive('isEnabled')->andReturnTrue();
    $engagement->shouldReceive('findRecipients')->andReturn(collect());
    $engagement->shouldNotReceive('dispatchForRecipient');

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('forEachTenant')
        ->once()
        ->andReturnUsing(function (callable $callback) use ($settings) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'test-bakery';

            $callback($tenant, $settings);

            return 0;
        });

    $output = Mockery::mock(Command::class);
    $output->shouldReceive('info')->never();

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(0);
});

test('handles recipient dispatch failure gracefully', function () {
    $customer = Mockery::mock(Customer::class)->shouldIgnoreMissing();
    $customer->name = 'Jane Doe';
    $customer->email = 'jane@example.com';

    $recipient = new EngagementRecipient(
        email: 'jane@example.com',
        name: 'Jane Doe',
        model: $customer,
    );

    $settings = makeFakeTenantSettings();

    $engagement = Mockery::mock(CustomerEngagement::class);
    $engagement->shouldReceive('isEnabled')->andReturnTrue();
    $engagement->shouldReceive('findRecipients')->andReturn(collect([$recipient]));
    $engagement->shouldReceive('dispatchForRecipient')
        ->andThrow(new RuntimeException('Mail server down'));

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('forEachTenant')
        ->once()
        ->andReturnUsing(function (callable $callback) use ($settings) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'test-bakery';

            $callback($tenant, $settings);

            return 0;
        });

    $output = Mockery::mock(Command::class);
    $output->shouldReceive('error')->once();

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(0);
});

test('calls error callback when tenant processing fails', function () {
    $engagement = Mockery::mock(CustomerEngagement::class);

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('forEachTenant')
        ->once()
        ->andReturnUsing(function (callable $callback, ?callable $onError) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'failing-bakery';

            $onError($tenant, new RuntimeException('DB connection failed'));

            return 1;
        });

    $output = Mockery::mock(Command::class);
    $output->shouldReceive('error')->once();

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(1);
});
