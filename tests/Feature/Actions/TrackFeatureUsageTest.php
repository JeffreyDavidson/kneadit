<?php

use App\Actions\TrackFeatureUsage;
use App\Models\FeatureUsageLog;

beforeEach(fn () => setUpCentralTest());

test('it creates a new usage record', function () {
    $log = app(TrackFeatureUsage::class)('tenant-1', 'recipe_import');

    $found = FeatureUsageLog::query()
        ->where('tenant_id', 'tenant-1')
        ->where('feature', 'recipe_import')
        ->first();

    expect($found)->not->toBeNull();
    expect($found->usage_count)->toBe(1);
});

test('it increments an existing usage record', function () {
    app(TrackFeatureUsage::class)('tenant-1', 'recipe_import');
    app(TrackFeatureUsage::class)('tenant-1', 'recipe_import');

    $log = FeatureUsageLog::query()
        ->where('tenant_id', 'tenant-1')
        ->where('feature', 'recipe_import')
        ->first();

    expect($log->usage_count)->toBe(2);
});
