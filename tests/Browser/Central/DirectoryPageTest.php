<?php

use Illuminate\Support\Facades\Config;

$centralUrl = Config::string('browser-testing.central_url');

test('directory page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/directory")
        ->assertNoJavaScriptErrors();
});
