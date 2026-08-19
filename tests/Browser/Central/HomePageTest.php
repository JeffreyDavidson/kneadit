<?php

$centralUrl = Illuminate\Support\Facades\Config::string('browser-testing.central_url');

test('central landing page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}")
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
