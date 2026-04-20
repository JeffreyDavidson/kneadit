<?php

use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('storefront layout receives tenant settings', function () {
    settings(['store_name' => 'Sweet Dreams Bakery']);

    $settings = app(TenantSettings::class);

    expect($settings->store->name)->toBe('Sweet Dreams Bakery');
});
