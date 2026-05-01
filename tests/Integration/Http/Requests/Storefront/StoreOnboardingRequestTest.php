<?php

use App\Http\Requests\Storefront\StoreOnboardingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $data = validOnboardingData();
    unset($data[$field]);

    $validator = validator($data, (new StoreOnboardingRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['store_name', 'subdomain', 'storefront_choice']);

test('subdomain must be alpha_dash', function () {
    $validator = validator(
        array_merge(validOnboardingData(), ['subdomain' => 'has spaces!']),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('subdomain'))->toBeTrue();
});

test('reserved subdomains are rejected', function (string $reserved) {
    $validator = validator(
        array_merge(validOnboardingData(), ['subdomain' => $reserved]),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('subdomain'))->toBeTrue();
})->with(['www', 'mail', 'admin', 'api', 'app']);

test('storefront_choice must be kneadit or own', function () {
    $validator = validator(
        array_merge(validOnboardingData(), ['storefront_choice' => 'wordpress']),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('storefront_choice'))->toBeTrue();
});

test('external_website is required when choosing own storefront', function () {
    $validator = validator(
        array_merge(validOnboardingData(), [
            'storefront_choice' => 'own',
            'external_website' => null,
        ]),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('external_website'))->toBeTrue();
});

test('external_website must be a valid url when provided', function () {
    $validator = validator(
        array_merge(validOnboardingData(), [
            'storefront_choice' => 'own',
            'external_website' => 'not-a-url',
        ]),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('external_website'))->toBeTrue();
});

test('valid input passes', function () {
    $validator = validator(validOnboardingData(), (new StoreOnboardingRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

function validOnboardingData(): array
{
    return [
        'store_name' => 'Sweet Bakes',
        'subdomain' => 'sweet-bakes-' . uniqid(),
        'storefront_choice' => 'kneadit',
    ];
}
