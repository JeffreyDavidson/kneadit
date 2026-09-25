<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'https://app.getkneadit.test');

test('pricing page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/pricing")
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
