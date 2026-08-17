<?php

use App\Models\Operations\WebhookDelivery;
use App\Services\Tenants\TenancyManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Double;

beforeEach(function () {
    setUpTenantTest();

    // The command iterates real central-DB tenants, but the test environment
    // has no central tenants table — stub the iterator so it just calls
    // through with the test's already-active tenant context.
    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')
        ->resolves(function (...$arguments) {
            $callback = $arguments[0] ?? null;

            throw_unless(is_callable($callback), UnexpectedValueException::class, 'Expected a tenant callback.');

            $callback(new App\Models\Platform\Tenant(['id' => 'test-tenant']));

            return 0;
        });

    app()->instance(TenancyManager::class, $tenancyManager);
});

pest()->use(RefreshDatabase::class);

test('prune deletes rows older than the default 30-day window', function () {
    $stale = WebhookDelivery::factory()->create(['dispatched_at' => now()->subDays(45)]);
    $fresh = WebhookDelivery::factory()->create(['dispatched_at' => now()->subDays(5)]);

    pendingArtisan('webhooks:prune')->assertSuccessful();

    expect(WebhookDelivery::find($stale->id))->toBeNull()
        ->and(WebhookDelivery::find($fresh->id))->not->toBeNull();
});

test('prune respects --days option', function () {
    $sevenDays = WebhookDelivery::factory()->create(['dispatched_at' => now()->subDays(7)]);
    $oneDay = WebhookDelivery::factory()->create(['dispatched_at' => now()->subDays(1)]);

    pendingArtisan('webhooks:prune', ['--days' => 3])->assertSuccessful();

    expect(WebhookDelivery::find($sevenDays->id))->toBeNull()
        ->and(WebhookDelivery::find($oneDay->id))->not->toBeNull();
});
