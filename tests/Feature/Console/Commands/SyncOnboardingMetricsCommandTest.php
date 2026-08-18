<?php

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantOnboardingMetrics;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\PendingCommand;
use JMac\Testing\Double;

use function Pest\Laravel\artisan;

beforeEach(fn () => setUpCentralTest());

function syncOnboardingMetricsCommand(): PendingCommand
{
    $command = artisan('tenants:sync-onboarding-metrics');

    throw_unless($command instanceof PendingCommand, RuntimeException::class, 'Console output must be mocked.');

    return $command;
}

test('command synchronizes every tenant', function () {
    $tenants = Tenant::factory()->count(2)->create();
    $metrics = Double::for(TenantOnboardingMetrics::class);
    $metrics->expects('sync')->times($tenants->count());

    app()->instance(TenantOnboardingMetrics::class, $metrics);

    syncOnboardingMetricsCommand()
        ->expectsOutput('Onboarding metrics synced: 2; failed: 0.')
        ->assertSuccessful();
});

test('command reports failures without hiding them', function () {
    Tenant::factory()->create();
    $metrics = Double::for(TenantOnboardingMetrics::class);
    $metrics->expects('sync')->times(1)->throws(new RuntimeException('tenant database unavailable'));

    app()->instance(TenantOnboardingMetrics::class, $metrics);
    Log::shouldReceive('warning')->once();

    syncOnboardingMetricsCommand()
        ->expectsOutput('Onboarding metrics synced: 0; failed: 1.')
        ->assertFailed();
});
