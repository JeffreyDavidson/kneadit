<?php

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('blog index page renders', function () {
    get(route('blog.index'))->assertOk();
});

test('index validates category against allowed values', function () {
    get(route('blog.index', ['category' => 'malicious-value']))
        ->assertRedirect();
});
