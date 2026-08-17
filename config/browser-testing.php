<?php

return [
    'central_url' => env('BROWSER_TEST_CENTRAL_URL', env('APP_URL', 'http://kneadit.test')),
    'storefront_url' => env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test'),
];
