<?php

use App\Support\Csp\CspNonce;

test('value() returns a non-empty url-safe base64 string', function () {
    $nonce = new CspNonce;

    expect($nonce->value())
        ->toBeString()
        ->not->toBeEmpty()
        ->toMatch('/^[A-Za-z0-9_-]+$/');
});

test('value() is stable across calls on the same instance', function () {
    $nonce = new CspNonce;

    expect($nonce->value())->toBe($nonce->value());
});

test('two instances produce different values', function () {
    expect((new CspNonce)->value())->not->toBe((new CspNonce)->value());
});

test('sourceList() wraps the value as a CSP source-list token', function () {
    $nonce = new CspNonce;

    expect($nonce->sourceList())->toBe("'nonce-{$nonce->value()}'");
});
