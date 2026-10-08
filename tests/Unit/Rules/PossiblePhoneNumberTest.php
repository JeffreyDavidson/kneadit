<?php

use App\Rules\PossiblePhoneNumber;
use Illuminate\Support\Facades\Validator;

test('accepts complete phone numbers', function (string $phone) {
    $validator = Validator::make(['phone' => $phone], ['phone' => [new PossiblePhoneNumber]]);

    expect($validator->passes())->toBeTrue();
})->with([
    'US E.164' => ['+19133877359'],
    'UK E.164' => ['+442079460958'],
    'US without a country code' => ['(913) 387-7359'],
]);

test('rejects numbers that are not complete for their country', function (mixed $phone) {
    $validator = Validator::make(['phone' => $phone], ['phone' => [new PossiblePhoneNumber]]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('phone'))->toBe('Enter a complete phone number for the selected country.');
})->with([
    'US number missing a digit' => ['+1913387735'],
    'UK number too short' => ['+44207946'],
    'local seven digits' => ['555-0100'],
    'letters' => ['call me'],
    'not a string' => [[9133877359]],
]);
