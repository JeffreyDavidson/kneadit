<?php

use App\Rules\SafeWebhookUrl;

test('public HTTPS addresses are allowed', function (string $url) {
    expect(resolve(SafeWebhookUrl::class)->isSafe($url))->toBeTrue();
})->with([
    'public IPv4 address' => 'https://8.8.8.8/webhooks/orders',
    'public IPv6 address' => 'https://[2001:4860:4860::8888]/webhooks/orders',
]);

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
    'carrier-grade NAT address' => 'https://100.100.100.200/latest/meta-data',
    'benchmarking address' => 'https://198.18.0.1/webhooks/orders',
    'IETF protocol assignment address' => 'https://192.0.0.8/webhooks/orders',
    'documentation address' => 'https://203.0.113.5/webhooks/orders',
    'multicast address' => 'https://224.0.0.1/webhooks/orders',
    'reserved address' => 'https://240.0.0.1/webhooks/orders',
    'this-network address' => 'https://0.1.2.3/webhooks/orders',
    'NAT64 address embedding the metadata IP' => 'https://[64:ff9b::a9fe:a9fe]/webhooks/orders',
    'NAT64 local-use address' => 'https://[64:ff9b:1::1]/webhooks/orders',
    '6to4 address embedding loopback' => 'https://[2002:7f00:1::]/webhooks/orders',
    'IPv6 documentation address' => 'https://[2001:db8::1]/webhooks/orders',
    'IPv6 unique local address' => 'https://[fd00::1]/webhooks/orders',
    'IPv6 link-local address' => 'https://[fe80::1]/webhooks/orders',
    'IPv6 multicast address' => 'https://[ff02::1]/webhooks/orders',
    'IPv4-mapped loopback address' => 'https://[::ffff:127.0.0.1]/webhooks/orders',
    'IPv4-mapped carrier-grade NAT address' => 'https://[::ffff:100.100.100.200]/webhooks/orders',
    'IPv4-compatible metadata address' => 'https://[::169.254.169.254]/webhooks/orders',
]);
