<?php

use App\Support\PhoneNumber;

test('normalize stores a number in E.164', function (string $typed, string $country, string $stored) {
    $result = PhoneNumber::normalize($typed, $country);

    expect($result)->toBe($stored);
})->with([
    'US national format' => ['(913) 387-7359', 'US', '+19133877359'],
    'US without a country code' => ['9133877359', 'US', '+19133877359'],
    'US with the leading 1' => ['19133877359', 'US', '+19133877359'],
    'US in E.164' => ['+1 913 387 7359', 'US', '+19133877359'],
    'UK national format' => ['020 7946 0958', 'GB', '+442079460958'],
    'UK in E.164 while the default is US' => ['+44 20 7946 0958', 'US', '+442079460958'],
]);

test('normalize keeps a value that is not a complete number exactly as typed', function (string $typed) {
    $result = PhoneNumber::normalize($typed, 'US');

    expect($result)->toBe(trim($typed));
})->with([
    'local seven digits' => ['555-0100'],
    'letters' => ['ask at the counter'],
    'too long' => ['91338773591234'],
    'with an extension' => ['913-387-7359 x12'],
    'UK national without its country' => ['020 7946 0958'],
]);

test('normalize turns blank into null', function (?string $typed) {
    expect(PhoneNumber::normalize($typed, 'US'))->toBeNull();
})->with([
    'null' => [null],
    'empty' => [''],
    'spaces' => ['   '],
]);

test('a number without a country code is read as a US number when no bakery is active', function () {
    $result = PhoneNumber::normalize('913-387-7359');

    expect($result)->toBe('+19133877359')
        ->and(PhoneNumber::homeCountry())->toBe('US');
});

test('isPossible accepts complete numbers for their country only', function (string $value, string $country, bool $possible) {
    expect(PhoneNumber::isPossible($value, $country))->toBe($possible);
})->with([
    'US E.164' => ['+19133877359', 'US', true],
    'UK E.164' => ['+442079460958', 'US', true],
    'US national' => ['(913) 387-7359', 'US', true],
    'UK national for a UK bakery' => ['020 7946 0958', 'GB', true],
    'US number missing a digit' => ['(913) 387-735', 'US', false],
    'local seven digits' => ['555-0100', 'US', false],
    'UK number too short' => ['+44 20 7946', 'US', false],
    'not a number' => ['hello', 'US', false],
]);

test('display shows the national format for the bakery country and international otherwise', function (string $stored, string $homeCountry, string $shown) {
    expect(PhoneNumber::display($stored, $homeCountry))->toBe($shown);
})->with([
    'US number at a US bakery' => ['+19133877359', 'US', '(913) 387-7359'],
    'UK number at a US bakery' => ['+442079460958', 'US', '+44 20 7946 0958'],
    'UK number at a UK bakery' => ['+442079460958', 'GB', '020 7946 0958'],
    'US number at a UK bakery' => ['+19133877359', 'GB', '+1 913-387-7359'],
    'unassigned US number at a US bakery' => ['+15551234567', 'US', '(555) 123-4567'],
]);

test('display shows a value that could not be read as it was stored', function (?string $stored, string $shown) {
    expect(PhoneNumber::display($stored, 'US'))->toBe($shown);
})->with([
    'local seven digits' => ['555-0100', '555-0100'],
    'letters' => ['ask at the counter', 'ask at the counter'],
    'null' => [null, ''],
]);

test('telUri links to E.164', function (string $stored, string $uri) {
    expect(PhoneNumber::telUri($stored))->toBe($uri);
})->with([
    'E.164' => ['+19133877359', 'tel:+19133877359'],
    'formatted' => ['(913) 387-7359', 'tel:+19133877359'],
    'unreadable keeps the digits' => ['555-0100', 'tel:5550100'],
]);

test('country reads the country of a stored number', function (?string $stored, ?string $country) {
    expect(PhoneNumber::country($stored))->toBe($country);
})->with([
    'US' => ['+19133877359', 'US'],
    'UK' => ['+442079460958', 'GB'],
    'no country code' => ['9133877359', null],
    'null' => [null, null],
]);

test('callingCodeAndNationalNumber splits a number for payment providers', function (?string $stored, ?array $parts) {
    expect(PhoneNumber::callingCodeAndNationalNumber($stored))->toBe($parts);
})->with([
    'US' => ['+19133877359', ['1', '9133877359']],
    'UK' => ['+442079460958', ['44', '2079460958']],
    'unreadable' => ['555-0100', null],
    'null' => [null, null],
]);
