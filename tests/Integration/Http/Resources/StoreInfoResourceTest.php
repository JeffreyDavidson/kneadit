<?php

use App\Http\Resources\StoreInfoResource;
use App\Services\Settings\TenantSettings;

beforeEach(fn () => setUpTenantTest());

it('exposes the expected JSON:API shape for store info', function () {
    $settings = app(TenantSettings::class);

    $resource = new StoreInfoResource($settings);
    $request = request();

    expect($resource->toType($request))->toBe('store-info')
        ->and($resource->toId($request))->toBe('current');

    $attributes = $resource->toAttributes($request);

    expect($attributes)
        ->toHaveKeys(['store_name', 'tagline', 'phone', 'email', 'address', 'logo_url', 'colors', 'hours', 'social_links'])
        ->and($attributes['store_name'])->toBeString()
        ->and($attributes['colors'])->toHaveKey('primary');
});
