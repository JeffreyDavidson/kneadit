<?php

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('directory page renders', function () {
    get(route('directory'))->assertOk();
});
