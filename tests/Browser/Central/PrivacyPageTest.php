<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'https://app.getkneadit.test');

test('privacy page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/privacy")
        ->assertNoJavaScriptErrors();
});
