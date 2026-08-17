<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

test('central landing page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}")
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
