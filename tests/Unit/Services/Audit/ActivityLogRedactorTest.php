<?php

use App\Models\Customers\Customer;
use App\Services\Audit\ActivityLogRedactor;

test('redacts denylisted keys but keeps the key', function (string $key) {
    $redacted = (new ActivityLogRedactor)->redactChanges(new Customer, [$key => 'secret-value', 'name' => 'Ada']);

    expect($redacted)->toBe([$key => '[redacted]', 'name' => 'Ada']);
})->with([
    'password',
    'remember_token',
    'api_token',
    'webhook_secret',
    'two_factor_secret',
    'two_factor_recovery_codes',
]);

test('redacts the hidden attributes of the model', function () {
    $customer = (new Customer)->makeHidden('notes');

    $redacted = (new ActivityLogRedactor)->redactChanges($customer, ['notes' => 'Allergic to nuts', 'city' => 'Leeds']);

    expect($redacted)->toBe(['notes' => '[redacted]', 'city' => 'Leeds']);
});

test('redacts denylisted keys in stored properties and the given hidden keys', function () {
    $properties = [
        'changes' => ['password' => '$2y$12$hash', 'notes' => 'Allergic to nuts', 'name' => 'Ada'],
    ];

    expect((new ActivityLogRedactor)->redactProperties($properties, ['notes']))->toBe([
        'changes' => ['password' => '[redacted]', 'notes' => '[redacted]', 'name' => 'Ada'],
    ]);
});

test('redacting stored properties twice gives the same result', function () {
    $redactor = new ActivityLogRedactor;
    $once = $redactor->redactProperties(['changes' => ['password' => 'hash']]);

    expect($redactor->redactProperties($once))->toBe($once);
});

test('redacts the named personal keys', function () {
    $properties = ['changes' => ['name' => 'Ada', 'email' => 'ada@example.com', 'status' => 'active']];

    expect((new ActivityLogRedactor)->redactKeys($properties, ['name', 'email']))->toBe([
        'changes' => ['name' => '[redacted]', 'email' => '[redacted]', 'status' => 'active'],
    ]);
});
