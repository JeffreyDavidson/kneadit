<?php

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
