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

test('subdomain longer than 63 characters is rejected', function () {
    $validator = validator(
        array_merge(validOnboardingData(), ['subdomain' => str_repeat('a', 64)]),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('subdomain'))->toBeTrue();
});

test('external_website longer than 255 characters is rejected', function () {
    $longUrl = 'https://example.com/' . str_repeat('a', 240);
    $validator = validator(
        array_merge(validOnboardingData(), [
            'storefront_choice' => 'own',
            'external_website' => $longUrl,
        ]),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('external_website'))->toBeTrue();
});

test('adminUrl uses https scheme when the request was made over HTTPS', function () {
    $request = StoreOnboardingRequest::create('https://kneadit.test/onboarding', 'POST', [
        'store_name' => 'Sweet Bakes',
        'subdomain' => 'sweet-bakes',
        'storefront_choice' => 'kneadit',
    ]);
    $request->setValidator(validator($request->all(), $request->rules()));

    expect($request->adminUrl())->toBe('https://sweet-bakes.kneadit.test/admin');
});

test('adminUrl uses http scheme when the request was made over plain HTTP', function () {
    $request = StoreOnboardingRequest::create('http://kneadit.test/onboarding', 'POST', [
        'store_name' => 'Sweet Bakes',
        'subdomain' => 'sweet-bakes',
        'storefront_choice' => 'kneadit',
    ]);
    $request->setValidator(validator($request->all(), $request->rules()));

    expect($request->adminUrl())->toBe('http://sweet-bakes.kneadit.test/admin');
});

test('referralCode reads from session first, falling back to cookie', function () {
    // session value present → use it
    $request = StoreOnboardingRequest::create('/onboarding', 'POST', [], [], [], [
        'HTTP_COOKIE' => 'referral_code=COOKIEVAL',
    ]);
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('referral_code', 'SESSIONVAL');

    expect($request->referralCode())->toBe('SESSIONVAL');
});

test('referralCode falls back to the cookie when no session value is set', function () {
    $request = StoreOnboardingRequest::create('/onboarding', 'POST', [], [
        'referral_code' => 'COOKIEVAL',
    ]);
    $request->setLaravelSession(app('session.store'));

    expect($request->referralCode())->toBe('COOKIEVAL');
});

test('referralCode returns null when neither session nor cookie has a value', function () {
    $request = StoreOnboardingRequest::create('/onboarding', 'POST');
    $request->setLaravelSession(app('session.store'));

    expect($request->referralCode())->toBeNull();
});

function validOnboardingData(): array
{
    return [
        'store_name' => 'Sweet Bakes',
        'subdomain' => 'sweet-bakes-' . uniqid(),
        'storefront_choice' => 'kneadit',
    ];
}
