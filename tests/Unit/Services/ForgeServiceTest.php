<?php

use App\Services\Platform\ForgeService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.forge.token' => 'test-token',
        'services.forge.organization' => 'test-organization',
        'services.forge.server_id' => '111',
        'services.forge.site_id' => '222',
    ]);
});

test('isConfigured returns false when token is missing', function () {
    config(['services.forge.token' => '']);

    expect(ForgeService::isConfigured())->toBeFalse();
});

test('isConfigured returns false when organization is missing', function () {
    config(['services.forge.organization' => '']);

    expect(ForgeService::isConfigured())->toBeFalse();
});

test('isConfigured returns true when all config is set', function () {
    expect(ForgeService::isConfigured())->toBeTrue();
});

test('isConfigured returns false when server_id is missing', function () {
    config(['services.forge.server_id' => '']);

    expect(ForgeService::isConfigured())->toBeFalse();
});

test('addDomainAlias creates a Forge v2 domain', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::sequence()
            ->push(['data' => [], 'meta' => ['next_cursor' => null]])
            ->push(['data' => ['id' => '333']], 202),
    ]);

    $result = resolve(ForgeService::class)->addDomainAlias('new-domain.com');

    expect($result)->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains'
        && $request->data() === [
            'name' => 'new-domain.com',
            'allow_wildcard_subdomains' => false,
            'www_redirect_type' => 'none',
        ]
        && $request->hasHeader('Authorization', 'Bearer test-token')
        && $request->hasHeader('Accept', 'application/vnd.api+json')
        && $request->hasHeader('Content-Type', 'application/vnd.api+json'));
});

test('addDomainAlias returns true when domain already exists', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([
            'data' => [[
                'id' => '333',
                'attributes' => ['name' => 'already-here.com'],
            ]],
            'meta' => ['next_cursor' => null],
        ]),
    ]);

    $result = resolve(ForgeService::class)->addDomainAlias('already-here.com');

    expect($result)->toBeTrue();

    Http::assertSentCount(1);
});

test('addDomainAlias follows domain pagination', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::sequence()
            ->push([
                'data' => [],
                'meta' => ['next_cursor' => 'next-page'],
            ])
            ->push([
                'data' => [[
                    'id' => '333',
                    'attributes' => ['name' => 'paginated.com'],
                ]],
                'meta' => ['next_cursor' => null],
            ]),
    ]);

    $result = resolve(ForgeService::class)->addDomainAlias('paginated.com');

    expect($result)->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && str_contains($request->url(), 'page%5Bcursor%5D=next-page'));
    Http::assertSentCount(2);
});

test('addDomainAlias returns false when listing domains fails', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([], 500),
    ]);

    $result = resolve(ForgeService::class)->addDomainAlias('test.com');

    expect($result)->toBeFalse();
});

test('addDomainAlias returns false when domain creation fails', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::sequence()
            ->push(['data' => [], 'meta' => ['next_cursor' => null]])
            ->push([], 422),
    ]);

    $result = resolve(ForgeService::class)->addDomainAlias('failing.com');

    expect($result)->toBeFalse();
});

test('obtainSslCertificate requests a Forge v2 certificate for the domain', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains/333/certificates' => Http::response([
            'data' => ['id' => '444'],
        ], 202),
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([
            'data' => [[
                'id' => '333',
                'attributes' => ['name' => 'secure.com'],
            ]],
            'meta' => ['next_cursor' => null],
        ]),
    ]);

    $result = resolve(ForgeService::class)->obtainSslCertificate('secure.com');

    expect($result)->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains/333/certificates'
        && $request->data() === ['type' => 'letsencrypt']);
});

test('obtainSslCertificate returns false when domain does not exist', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([
            'data' => [],
            'meta' => ['next_cursor' => null],
        ]),
    ]);

    $result = resolve(ForgeService::class)->obtainSslCertificate('missing.com');

    expect($result)->toBeFalse();

    Http::assertSentCount(1);
});

test('obtainSslCertificate returns false on failure', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains/333/certificates' => Http::response([], 500),
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([
            'data' => [[
                'id' => '333',
                'attributes' => ['name' => 'bad.com'],
            ]],
            'meta' => ['next_cursor' => null],
        ]),
    ]);

    $result = resolve(ForgeService::class)->obtainSslCertificate('bad.com');

    expect($result)->toBeFalse();
});

test('removeDomainAlias deletes the Forge v2 domain', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([
            'data' => [[
                'id' => '333',
                'attributes' => ['name' => 'remove.com'],
            ]],
            'meta' => ['next_cursor' => null],
        ]),
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains/333' => Http::response(status: 204),
    ]);

    $result = resolve(ForgeService::class)->removeDomainAlias('remove.com');

    expect($result)->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'DELETE'
        && $request->url() === 'https://forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains/333');
});

test('removeDomainAlias returns true when domain is already absent', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([
            'data' => [],
            'meta' => ['next_cursor' => null],
        ]),
    ]);

    $result = resolve(ForgeService::class)->removeDomainAlias('missing.com');

    expect($result)->toBeTrue();

    Http::assertSentCount(1);
});

test('removeDomainAlias returns false when listing domains fails', function () {
    Http::fake([
        'forge.laravel.com/api/orgs/test-organization/servers/111/sites/222/domains*' => Http::response([], 500),
    ]);

    $result = resolve(ForgeService::class)->removeDomainAlias('test.com');

    expect($result)->toBeFalse();
});
