<?php

use App\Actions\Platform\TrackFeatureUsage;
use App\Models\Platform\FeatureUsageLog;

beforeEach(fn () => setUpCentralTest());

test('track creates new record', function () {
    $log = resolve(TrackFeatureUsage::class)('tenant-1', 'recipe_import');

    $found = FeatureUsageLog::query()->where('tenant_id', 'tenant-1')->where('feature', 'recipe_import')->firstOrFail();
    expect($found)->not->toBeNull()->and($found->usage_count)->toBe(1);
});

test('track increments existing record', function () {
    resolve(TrackFeatureUsage::class)('tenant-1', 'recipe_import');
    resolve(TrackFeatureUsage::class)('tenant-1', 'recipe_import');

    $log = FeatureUsageLog::query()->where('tenant_id', 'tenant-1')
        ->where('feature', 'recipe_import')
        ->firstOrFail();

    expect($log->usage_count)->toBe(2)->and(FeatureUsageLog::query()->where('tenant_id', 'tenant-1')->where('feature', 'recipe_import')->get())->toHaveCount(1);
});
