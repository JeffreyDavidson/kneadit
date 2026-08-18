<?php

use App\Rules\SafeWebhookUrl;

test('public HTTPS addresses are allowed', function () {
    expect(resolve(SafeWebhookUrl::class)->isSafe('https://8.8.8.8/webhooks/orders'))
        ->toBeTrue();
});

test('unsafe webhook destinations are rejected', function (string $url) {
    expect(resolve(SafeWebhookUrl::class)->isSafe($url))->toBeFalse();
})->with([
    'unencrypted URL' => 'http://8.8.8.8/webhooks/orders',
    'loopback IPv4 address' => 'https://127.0.0.1/webhooks/orders',
    'private IPv4 address' => 'https://10.0.0.1/webhooks/orders',
    'link-local IPv4 address' => 'https://169.254.169.254/latest/meta-data',
    'loopback IPv6 address' => 'https://[::1]/webhooks/orders',
    'URL credentials' => 'https://user:password@8.8.8.8/webhooks/orders',
    'missing host' => 'https:///webhooks/orders',
]);
