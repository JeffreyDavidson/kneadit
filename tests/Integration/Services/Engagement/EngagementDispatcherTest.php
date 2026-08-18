<?php

use App\Contracts\Engagement\CustomerEngagement;
use App\Contracts\Engagement\EngagementRecipient;
use App\Models\Customers\Customer;
use App\Services\Engagement\EngagementDispatcher;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Command;
use JMac\Testing\Double;

function makeFakeTenantSettings(): TenantSettings
{
    return makeTenantSettings();
}

test('dispatches engagement to recipients across tenants', function () {
    $customer = new Customer;
    $customer->name = 'Jane Doe';
    $customer->email = 'jane@example.com';

    $recipient = new EngagementRecipient(
        email: 'jane@example.com',
        name: 'Jane Doe',
        model: $customer,
    );

    $settings = makeFakeTenantSettings();

    $engagement = Double::for(CustomerEngagement::class);
    $engagement->allows('isEnabled')->with($settings)->returns(true);
    $engagement->allows('findRecipients')->with($settings)->returns(collect([$recipient]));
    $engagement->expects('dispatchForRecipient')->with($recipient, $settings);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')
        ->resolves(function (callable $callback, ?callable $onError) use ($settings) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'test-bakery';

            app()->instance(TenantSettings::class, $settings);

            $callback($tenant, $settings);

            return 0;
        });

    $output = Double::for(Command::class);
    $output->allows('info')->returns($output);
    $output->allows('error')->returns($output);

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(0);
});

test('skips disabled engagements', function () {
    $settings = makeFakeTenantSettings();

    $engagement = Double::for(CustomerEngagement::class);
    $engagement->allows('isEnabled')->with($settings)->returns(false);
    $engagement->expects('findRecipients')->never();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')
        ->resolves(function (callable $callback) use ($settings) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'test-bakery';

            $callback($tenant, $settings);

            return 0;
        });

    $output = Double::for(Command::class);
    $output->allows('info')->returns($output);

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(0);
});

test('skips when no recipients found', function () {
    $settings = makeFakeTenantSettings();

    $engagement = Double::for(CustomerEngagement::class);
    $engagement->allows('isEnabled')->returns(true);
    $engagement->allows('findRecipients')->returns(collect());
    $engagement->expects('dispatchForRecipient')->never();

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')
        ->resolves(function (callable $callback) use ($settings) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'test-bakery';

            $callback($tenant, $settings);

            return 0;
        });

    $output = Double::for(Command::class);
    $output->expects('info')->never();

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(0);
});

test('handles recipient dispatch failure gracefully', function () {
    $customer = new Customer;
    $customer->name = 'Jane Doe';
    $customer->email = 'jane@example.com';

    $recipient = new EngagementRecipient(
        email: 'jane@example.com',
        name: 'Jane Doe',
        model: $customer,
    );

    $settings = makeFakeTenantSettings();

    $engagement = Double::for(CustomerEngagement::class);
    $engagement->allows('isEnabled')->returns(true);
    $engagement->allows('findRecipients')->returns(collect([$recipient]));
    $engagement->allows('dispatchForRecipient')
        ->throws(new RuntimeException('Mail server down'));

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')
        ->resolves(function (callable $callback) use ($settings) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'test-bakery';

            $callback($tenant, $settings);

            return 0;
        });

    $output = Double::for(Command::class);
    $output->expects('error');

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(0);
});

test('calls error callback when tenant processing fails', function () {
    $engagement = Double::for(CustomerEngagement::class);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')
        ->resolves(function (callable $callback, ?callable $onError) {
            $tenant = new App\Models\Platform\Tenant;
            $tenant->id = 'failing-bakery';

            $onError($tenant, new RuntimeException('DB connection failed'));

            return 1;
        });

    $output = Double::for(Command::class);
    $output->expects('error');

    $dispatcher = new EngagementDispatcher($tenancyManager);
    $failures = $dispatcher->dispatch($engagement, $output);

    expect($failures)->toBe(1);
});
