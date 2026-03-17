<?php

use App\Models\FeatureUsageLog;
use Tests\CentralTestCase;

uses(CentralTestCase::class);

test('track creates new record', function () {
    $log = FeatureUsageLog::track('tenant-1', 'recipe_import');

    $found = FeatureUsageLog::where('tenant_id', 'tenant-1')->where('feature', 'recipe_import')->first();
    expect($found)->not->toBeNull();
    expect($found->usage_count)->toBe(1);
});

test('track increments existing record', function () {
    FeatureUsageLog::track('tenant-1', 'recipe_import');
    FeatureUsageLog::track('tenant-1', 'recipe_import');

    $log = FeatureUsageLog::where('tenant_id', 'tenant-1')
        ->where('feature', 'recipe_import')
        ->first();

    expect($log->usage_count)->toBe(2);
    expect(FeatureUsageLog::where('tenant_id', 'tenant-1')->where('feature', 'recipe_import')->get())->toHaveCount(1);
});
