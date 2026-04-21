<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

test('changelog page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/changelog")
        ->assertNoJavaScriptErrors();
});
