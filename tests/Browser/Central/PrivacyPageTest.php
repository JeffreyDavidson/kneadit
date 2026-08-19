<?php

$centralUrl = Illuminate\Support\Facades\Config::string('browser-testing.central_url');

test('privacy page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/privacy")
        ->assertNoJavaScriptErrors();
});
