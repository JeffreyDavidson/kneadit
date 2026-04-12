<?php

use App\Services\Platform\ForgeService;
use Illuminate\Support\Facades\Http;

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

test('isConfigured returns false when server_id is missing', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '',
        'services.forge.site_id' => '456',
    ]);

    expect(ForgeService::isConfigured())->toBeFalse();
});

test('addDomainAlias adds domain to site aliases', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);

    Http::fake([
        'forge.laravel.com/api/v1/servers/111/sites/222' => Http::sequence()
            ->push(['site' => ['aliases' => ['existing.com']]])
            ->push([], 200),
    ]);

    $service = new ForgeService;
    $result = $service->addDomainAlias('new-domain.com');

    expect($result)->toBeTrue();

    Http::assertSentCount(2);
});

test('addDomainAlias returns true when domain already exists', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);

    Http::fake([
        'forge.laravel.com/api/v1/servers/111/sites/222' => Http::response([
            'site' => ['aliases' => ['already-here.com']],
        ]),
    ]);

    $service = new ForgeService;
    $result = $service->addDomainAlias('already-here.com');

    expect($result)->toBeTrue();

    // Should only make one GET request, no PUT
    Http::assertSentCount(1);
});

test('addDomainAlias returns false when get site fails', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);

    Http::fake([
        'forge.laravel.com/api/v1/servers/111/sites/222' => Http::response([], 500),
    ]);

    $service = new ForgeService;
    $result = $service->addDomainAlias('test.com');

    expect($result)->toBeFalse();
});

test('addDomainAlias returns false when update fails', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);

    Http::fake([
        'forge.laravel.com/api/v1/servers/111/sites/222' => Http::sequence()
            ->push(['site' => ['aliases' => []]])
            ->push([], 422),
    ]);

    $service = new ForgeService;
    $result = $service->addDomainAlias('failing.com');

    expect($result)->toBeFalse();
});

test('obtainSslCertificate requests SSL for domain', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);

    Http::fake([
        'forge.laravel.com/api/v1/servers/111/sites/222/certificates/letsencrypt' => Http::response([], 200),
    ]);

    $service = new ForgeService;
    $result = $service->obtainSslCertificate('secure.com');

    expect($result)->toBeTrue();
});

test('obtainSslCertificate returns false on failure', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);

    Http::fake([
        'forge.laravel.com/api/v1/servers/111/sites/222/certificates/letsencrypt' => Http::response([], 500),
    ]);

    $service = new ForgeService;
    $result = $service->obtainSslCertificate('bad.com');

    expect($result)->toBeFalse();
});

test('removeDomainAlias removes domain from site aliases', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);

    Http::fake([
        'forge.laravel.com/api/v1/servers/111/sites/222' => Http::sequence()
            ->push(['site' => ['aliases' => ['keep.com', 'remove.com']]])
            ->push([], 200),
    ]);

    $service = new ForgeService;
    $result = $service->removeDomainAlias('remove.com');

    expect($result)->toBeTrue();
});

test('removeDomainAlias returns false when get site fails', function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);

    Http::fake([
        'forge.laravel.com/api/v1/servers/111/sites/222' => Http::response([], 500),
    ]);

    $service = new ForgeService;
    $result = $service->removeDomainAlias('test.com');

    expect($result)->toBeFalse();
});
