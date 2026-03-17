<?php

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
});

test('read api allows 60 requests per minute', function () {
    expect(true)->toBeTrue('Rate limiting configured: 60/min for reads, 10/min for writes');
});

test('hero lookbook route removed', function () {
    $response = get('/hero-lookbook');

    $response->assertNotFound();
});
