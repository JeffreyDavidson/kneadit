<?php

use App\Services\Settings\TenantSettings;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('order controller index passes settings to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('settings', fn (TenantSettings $s) => is_int($s->orders->leadTimeHours) && is_bool($s->orders->deliveryEnabled));
});

test('order controller passes page content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('content');
});
