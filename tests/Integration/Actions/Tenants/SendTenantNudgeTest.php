<?php

use App\Actions\Tenants\SendTenantNudge;
use App\Models\PlatformMessage;

beforeEach(fn () => setUpCentralTest());

test('creates a nudge message for the tenant', function () {
    createTenant(['store_name' => 'Sweet Dreams']);
    $tenant = App\Models\Tenant::query()->find('test-bakery');

    $message = resolve(SendTenantNudge::class)($tenant);

    expect($message)->toBeInstanceOf(PlatformMessage::class)
        ->and($message->tenant_id)->toBe('test-bakery')
        ->and($message->subject)->toContain("haven't been around")
        ->and($message->is_read)->toBeFalse();
});
