<?php

use App\Services\ForgeService;

test('isConfigured returns false when token is missing', function () {
    config(['services.forge.token' => '']);

    expect(ForgeService::isConfigured())->toBeFalse();
});

test('isConfigured returns true when all config is set', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '123',
        'services.forge.site_id' => '456',
    ]);

    expect(ForgeService::isConfigured())->toBeTrue();
});
