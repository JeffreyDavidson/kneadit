<?php

$centralUrl = Illuminate\Support\Facades\Config::string('browser-testing.central_url');

test('terms page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/terms")
        ->assertNoJavaScriptErrors();
});
