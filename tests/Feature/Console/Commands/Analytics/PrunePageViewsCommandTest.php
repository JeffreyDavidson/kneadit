<?php

use App\Models\Engagement\PageView;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    $tenancyManager = new class extends TenancyManager {
        public function forEachTenant(callable $callback, ?callable $onError = null): int
        {
            $callback(new Tenant(['id' => 'test-tenant']), resolve(TenantSettings::class));

            return 0;
        }
    };

    app()->instance(TenancyManager::class, $tenancyManager);
});

/** @param array<string, bool|int|string|null> $parameters */
function prunePageViewsCommand(array $parameters = []): PendingCommand
{
    return pendingArtisan('analytics:prune-page-views', $parameters);
}

test('it prunes page views outside the configured retention window', function () {
    config(['analytics.page_view_retention_days' => 90]);

    $stale = PageView::factory()->create(['created_at' => now()->subDays(91)]);
    $fresh = PageView::factory()->create(['created_at' => now()->subDays(89)]);

    prunePageViewsCommand()->assertSuccessful();

    expect(PageView::query()->find($stale->id))->toBeNull()
        ->and(PageView::query()->find($fresh->id))->not->toBeNull();
});

test('it accepts a positive retention override', function () {
    $stale = PageView::factory()->create(['created_at' => now()->subDays(8)]);
    $fresh = PageView::factory()->create(['created_at' => now()->subDays(6)]);

    prunePageViewsCommand(['--days' => 7])->assertSuccessful();

    expect(PageView::query()->find($stale->id))->toBeNull()
        ->and(PageView::query()->find($fresh->id))->not->toBeNull();
});

test('it rejects a non-positive retention window', function () {
    prunePageViewsCommand(['--days' => 0])->assertExitCode(2);
});

test('it fails when any tenant cannot be pruned', function () {
    app()->instance(TenancyManager::class, new class extends TenancyManager {
        public function forEachTenant(callable $callback, ?callable $onError = null): int
        {
            return 1;
        }
    });

    prunePageViewsCommand()->assertFailed();
});
