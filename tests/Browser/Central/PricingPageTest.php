<?php

$centralUrl = Illuminate\Support\Facades\Config::string('browser-testing.central_url');

test('pricing page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/pricing")
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
