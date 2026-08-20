<?php

use App\Filament\Central\Pages\BakeryInsights;
use App\Models\Platform\Tenant;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new BakeryInsights;
});

test('get tenant health data returns collection', function () {
    $result = testFixture('page', BakeryInsights::class)->getTenantHealthData();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get alerts returns collection', function () {
    $result = testFixture('page', BakeryInsights::class)->getAlerts();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get tenant usage data returns collection', function () {
    $result = testFixture('page', BakeryInsights::class)->getTenantUsageData();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('extend trial with missing tenant does not record extension', function () {
    testFixture('page', BakeryInsights::class)->extendTrial('non-existent-id');

    expect(testFixture('page', BakeryInsights::class)->extendedTrials)->toBeEmpty();
});

test('extend trial with valid tenant records trial extension', function () {
    $tenant = Tenant::factory()->onTrial()->create();

    testFixture('page', BakeryInsights::class)->extendTrial($tenant->id);

    expect(testFixture('page', BakeryInsights::class)->extendedTrials)->toContain($tenant->id);
});

test('send nudge with missing tenant does not record nudge', function () {
    testFixture('page', BakeryInsights::class)->sendNudge('non-existent-id');

    expect(testFixture('page', BakeryInsights::class)->sentNudges)->toBeEmpty();
});

test('send nudge with valid tenant records nudge', function () {
    $tenant = Tenant::factory()->create(['store_name' => 'Test Bakery']);

    testFixture('page', BakeryInsights::class)->sendNudge($tenant->id);

    expect(testFixture('page', BakeryInsights::class)->sentNudges)->toContain($tenant->id);
});

test('suggest upgrade does not throw', function () {
    testFixture('page', BakeryInsights::class)->suggestUpgrade('some-tenant-id');

    expect(true)->toBeTrue();
});
