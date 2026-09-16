<?php

use App\Services\Security\CspReportContextBuilder;

beforeEach(function () {
    config(['csp.report_fields' => ['blocked-uri', 'script-sample']]);
});

test('builds a bounded context from configured report fields', function () {
    $context = app(CspReportContextBuilder::class)->build([
        'blocked-uri' => 'https://example.test/script.js',
        'script-sample' => str_repeat('x', 3_000),
        'untrusted-extra-field' => 'must not enter logs',
    ]);

    expect($context)->toBe([
        'blocked-uri' => 'https://example.test/script.js',
        'script-sample' => str_repeat('x', 2_048),
    ]);
});

test('marks non-array reports as malformed', function () {
    expect(app(CspReportContextBuilder::class)->build('invalid'))
        ->toBe(['malformed' => true]);
});
