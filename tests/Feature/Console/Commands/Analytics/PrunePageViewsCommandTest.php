<?php

use App\Models\Engagement\PageView;
use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettingsRegistry;
use App\Services\Tenants\TenancyManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;

use function Pest\Laravel\artisan;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    $tenancyManager = new class extends TenancyManager {
        public function __construct()
        {
            parent::__construct(
                app(SettingsManager::class),
                app(TenantSettingsRegistry::class),
            );
        }

        public function forEachTenant(callable $callback, ?callable $onError = null): int
        {
            $callback(new Tenant(['id' => 'test-tenant']));

            return 0;
        }
    };

    app()->instance(TenancyManager::class, $tenancyManager);
});

/** @param array<string, bool|int|string|null> $parameters */
function prunePageViewsCommand(array $parameters = []): PendingCommand
{
    $command = artisan('analytics:prune-page-views', $parameters);

    throw_unless($command instanceof PendingCommand, RuntimeException::class, 'Console output must be mocked.');

    return $command;
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
