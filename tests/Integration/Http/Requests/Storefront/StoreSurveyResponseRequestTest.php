<?php

use App\Http\Requests\Storefront\StoreSurveyResponseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('answers is required', function () {
    $validator = validator([], (new StoreSurveyResponseRequest)->rules());

    expect($validator->errors()->has('answers'))->toBeTrue();
});

test('answers must be an array', function () {
    $validator = validator(['answers' => 'just a string'], (new StoreSurveyResponseRequest)->rules());

    expect($validator->errors()->has('answers'))->toBeTrue();
});

test('customer_email must be valid when provided', function () {
    $validator = validator([
        'answers' => ['q1' => 'a'],
        'customer_email' => 'not-email',
    ], (new StoreSurveyResponseRequest)->rules());

    expect($validator->errors()->has('customer_email'))->toBeTrue();
});

test('valid submission passes', function () {
    $validator = validator([
        'answers' => ['q1' => 'great', 'q2' => 5],
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
    ], (new StoreSurveyResponseRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('anonymous submission with only answers passes', function () {
    $validator = validator([
        'answers' => ['q1' => 'good'],
    ], (new StoreSurveyResponseRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
