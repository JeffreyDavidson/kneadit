<?php

use App\Actions\Tenants\ExtendTenantTrial;

beforeEach(fn () => setUpCentralTest());

test('extends tenant trial by specified days', function () {
    createTenant(['trial_ends_at' => now()->addDays(5)]);
    $tenant = App\Models\Platform\Tenant::query()->find('test-bakery');

    $newEnd = resolve(ExtendTenantTrial::class)($tenant, 14);

    expect($newEnd->toDateString())->toBe(now()->addDays(19)->toDateString());
});
