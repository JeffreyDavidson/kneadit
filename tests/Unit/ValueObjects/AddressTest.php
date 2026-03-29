<?php

use App\ValueObjects\Address;

test('address formats to full string', function () {
    $address = new Address(
        street: '123 Baker St',
        city: 'Springfield',
        state: 'IL',
        zip: '62701',
    );

    expect($address->formatted())->toBe('123 Baker St, Springfield, IL 62701');
});

test('address handles partial data', function () {
    $address = new Address(city: 'Springfield', state: 'IL');

    expect($address->formatted())->toBe('Springfield, IL')
        ->and($address->isEmpty())->toBeFalse();
});

test('address isEmpty returns true when all null', function () {
    $address = new Address;

    expect($address->isEmpty())->toBeTrue();
});

test('address fromArray handles both address and street keys', function () {
    $a = Address::fromArray(['address' => '123 Main St', 'city' => 'Test', 'state' => 'TX', 'zip' => '75001']);
    $b = Address::fromArray(['street' => '456 Oak Ave', 'city' => 'Other']);

    expect($a->street)->toBe('123 Main St')
        ->and($b->street)->toBe('456 Oak Ave');
});
