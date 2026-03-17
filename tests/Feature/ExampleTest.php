<?php

test('the application returns a successful response', function () {
    $response = get('/');

    $response->assertOk();
});
