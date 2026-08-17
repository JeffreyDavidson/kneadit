<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\postJson;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('csp report endpoint accepts a violation payload and returns 204', function () {
    $response = postJson(route('csp.report', [], false), [
        'csp-report' => [
            'document-uri' => 'https://example.test/admin',
            'violated-directive' => 'script-src',
            'blocked-uri' => 'inline',
        ],
    ]);

    $response->assertNoContent();
});

test('csp report endpoint accepts a bare body too (browser shape varies)', function () {
    $response = postJson(route('csp.report', [], false), [
        'violated-directive' => 'img-src',
        'blocked-uri' => 'https://example.test/tracker.gif',
    ]);

    $response->assertNoContent();
});

test('csp report endpoint logs only bounded diagnostic fields', function () {
    Log::shouldReceive('channel')->once()->with('stack')->andReturnSelf();
    Log::shouldReceive('warning')->once()->with('CSP violation report', [
        'blocked-uri' => 'https://example.test/script.js',
        'script-sample' => str_repeat('x', 2_048),
    ]);

    postJson(route('csp.report', [], false), [
        'csp-report' => [
            'blocked-uri' => 'https://example.test/script.js',
            'script-sample' => str_repeat('x', 3_000),
            'untrusted-extra-field' => 'must not enter logs',
        ],
    ])->assertNoContent();
});

test('csp report endpoint rejects oversized payloads before logging', function () {
    config(['csp.max_report_bytes' => 64]);
    Log::shouldReceive('channel')->never();

    postJson(route('csp.report', [], false), [
        'csp-report' => ['blocked-uri' => str_repeat('x', 128)],
    ])->assertStatus(Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
});
