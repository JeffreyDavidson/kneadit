<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\Tenant;

test('resource returns globally searchable attributes', function () {
    expect(TenantResource::getGloballySearchableAttributes())
        ->toBe(['store_name', 'name', 'email', 'id']);
});

test('resource returns global search result details', function () {
    $tenant = (new Tenant)->forceFill([
        'name' => 'Jane Baker',
        'plan' => SubscriptionTier::Growth,
    ]);

    $details = TenantResource::getGlobalSearchResultDetails($tenant);

    expect($details)
        ->toHaveKey('Owner', 'Jane Baker')
        ->toHaveKey('Plan', 'Growth');
});
