<?php

use App\Rules\SafeWebhookUrl;

/**
 * @param array<int, array<string, mixed>>|false $records
 */
function webhookRuleWithDnsRecords(array|false $records): SafeWebhookUrl
{
    return new class($records) extends SafeWebhookUrl {
        /** @param array<int, array<string, mixed>>|false $records */
        public function __construct(private array|false $records) {}

        protected function recordsForHost(string $host): array|false
        {
            return $this->records;
        }
    };
}

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

test('hostname is allowed only when every resolved address is public', function () {
    $rule = webhookRuleWithDnsRecords([
        ['ip' => '8.8.8.8'],
        ['ipv6' => '2606:4700:4700::1111'],
    ]);

    expect($rule->publicAddressFor('https://webhooks.example.com/orders'))->toBe('8.8.8.8');
});

test('hostname resolving to any private address is rejected', function (array|false $records) {
    expect(webhookRuleWithDnsRecords($records)->isSafe('https://webhooks.example.com/orders'))
        ->toBeFalse();
})->with([
    'private only' => [[['ip' => '10.0.0.1']]],
    'mixed public and private' => [[['ip' => '8.8.8.8'], ['ip' => '127.0.0.1']]],
    'no records' => [[]],
    'resolution failure' => [false],
]);
