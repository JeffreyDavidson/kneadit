<?php

use App\Actions\Platform\TrackFeatureUsage;
use App\Models\FeatureUsageLog;

beforeEach(fn () => setUpCentralTest());

test('track creates new record', function () {
    $log = app(TrackFeatureUsage::class)('tenant-1', 'recipe_import');

    $found = FeatureUsageLog::query()->where('tenant_id', 'tenant-1')->where('feature', 'recipe_import')->first();
    expect($found)->not->toBeNull();
    expect($found->usage_count)->toBe(1);
});

test('track increments existing record', function () {
    app(TrackFeatureUsage::class)('tenant-1', 'recipe_import');
    app(TrackFeatureUsage::class)('tenant-1', 'recipe_import');

    $log = FeatureUsageLog::query()->where('tenant_id', 'tenant-1')
        ->where('feature', 'recipe_import')
        ->first();

    expect($log->usage_count)->toBe(2);
    expect(FeatureUsageLog::query()->where('tenant_id', 'tenant-1')->where('feature', 'recipe_import')->get())->toHaveCount(1);
});
