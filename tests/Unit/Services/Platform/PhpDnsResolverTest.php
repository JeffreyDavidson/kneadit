<?php

use App\Services\Platform\PhpDnsResolver;

test('ipv4 returns the address a host resolves to', function () {
    expect(new PhpDnsResolver()->ipv4('localhost'))->toBe('127.0.0.1');
});

test('ipv4 returns null for a host that does not resolve', function () {
    expect(new PhpDnsResolver()->ipv4('does-not-exist.invalid'))->toBeNull();
});
