<?php

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('forgot password page renders', function () {
    $this->get('/forgot-password')->assertOk();
});
