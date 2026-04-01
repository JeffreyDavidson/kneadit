<?php

use App\Http\Resources\StoreInfoResource;
use App\Services\Settings\TenantSettings;

beforeEach(fn () => setUpTenantTest());

it('transforms tenant settings into the expected API shape', function () {
    $settings = app(TenantSettings::class);

    $resource = new StoreInfoResource($settings);
    $data = $resource->toArray(request());

    expect($data)
        ->toHaveKeys(['store_name', 'tagline', 'phone', 'email', 'address', 'logo_url', 'colors', 'hours', 'social_links'])
        ->and($data['store_name'])->toBeString()
        ->and($data['colors'])->toHaveKey('primary');
});
