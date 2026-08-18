<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

test('terms page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/terms")
        ->assertNoJavaScriptErrors();
});
