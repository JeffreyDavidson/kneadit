<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

test('directory page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/directory")
        ->assertNoJavaScriptErrors();
});
