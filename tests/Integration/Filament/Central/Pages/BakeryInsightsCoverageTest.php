<?php

use App\Filament\Central\Pages\BakeryInsights;
use App\Models\Platform\Tenant;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new BakeryInsights;
});

test('get tenant health data returns collection', function () {
    $result = test()->page->getTenantHealthData();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get alerts returns collection', function () {
    $result = test()->page->getAlerts();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get tenant usage data returns collection', function () {
    $result = test()->page->getTenantUsageData();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('extend trial with missing tenant does not record extension', function () {
    test()->page->extendTrial('non-existent-id');

    expect(test()->page->extendedTrials)->toBeEmpty();
});

test('extend trial with valid tenant records trial extension', function () {
    $tenant = Tenant::factory()->onTrial()->create();

    test()->page->extendTrial($tenant->id);

    expect(test()->page->extendedTrials)->toContain($tenant->id);
});

test('send nudge with missing tenant does not record nudge', function () {
    test()->page->sendNudge('non-existent-id');

    expect(test()->page->sentNudges)->toBeEmpty();
});

test('send nudge with valid tenant records nudge', function () {
    $tenant = Tenant::factory()->create(['store_name' => 'Test Bakery']);

    test()->page->sendNudge($tenant->id);

    expect(test()->page->sentNudges)->toContain($tenant->id);
});

test('suggest upgrade does not throw', function () {
    test()->page->suggestUpgrade('some-tenant-id');

    expect(true)->toBeTrue();
});
