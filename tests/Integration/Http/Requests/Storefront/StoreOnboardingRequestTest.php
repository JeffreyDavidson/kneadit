<?php

use App\Http\Requests\Storefront\StoreOnboardingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function () {
    foreach (['store_name', 'subdomain', 'storefront_choice'] as $field) {
        $data = validOnboardingData();
        unset($data[$field]);

        $validator = validator($data, (new StoreOnboardingRequest)->rules());

        expect($validator->errors()->has($field))->toBeTrue();
    }
});

test('subdomain constraints are enforced', function () {
    $validator = validator(
        array_merge(validOnboardingData(), ['subdomain' => 'has spaces!']),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('subdomain'))->toBeTrue();

    foreach (['www', 'mail', 'admin', 'api', 'app'] as $subdomain) {
        $validator = validator(
            array_merge(validOnboardingData(), ['subdomain' => $subdomain]),
            (new StoreOnboardingRequest)->rules(),
        );

        expect($validator->errors()->has('subdomain'))->toBeTrue();
    }

    $validator = validator(
        array_merge(validOnboardingData(), ['subdomain' => str_repeat('a', 64)]),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('subdomain'))->toBeTrue();
});

test('storefront and external website constraints are enforced', function () {
    $validator = validator(
        array_merge(validOnboardingData(), ['storefront_choice' => 'wordpress']),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('storefront_choice'))->toBeTrue();

    $validator = validator(
        array_merge(validOnboardingData(), [
            'storefront_choice' => 'own',
            'external_website' => null,
        ]),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('external_website'))->toBeTrue();

    $validator = validator(
        array_merge(validOnboardingData(), [
            'storefront_choice' => 'own',
            'external_website' => 'not-a-url',
        ]),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('external_website'))->toBeTrue();

    $validator = validator(
        array_merge(validOnboardingData(), [
            'storefront_choice' => 'own',
            'external_website' => 'https://example.com/' . str_repeat('a', 240),
        ]),
        (new StoreOnboardingRequest)->rules(),
    );

    expect($validator->errors()->has('external_website'))->toBeTrue();
});

test('valid input passes', function () {
    $validator = validator(validOnboardingData(), (new StoreOnboardingRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('adminUrl preserves the request scheme', function () {
    $secureRequest = StoreOnboardingRequest::create('https://kneadit.test/onboarding', 'POST', [
        'store_name' => 'Sweet Bakes',
        'subdomain' => 'sweet-bakes',
        'storefront_choice' => 'kneadit',
    ]);
    $secureRequest->setValidator(validator($secureRequest->all(), $secureRequest->rules()));

    expect($secureRequest->adminUrl())->toBe('https://sweet-bakes.kneadit.test/admin');

    $plainRequest = StoreOnboardingRequest::create('http://kneadit.test/onboarding', 'POST', [
        'store_name' => 'Sweet Bakes',
        'subdomain' => 'sweet-bakes',
        'storefront_choice' => 'kneadit',
    ]);
    $plainRequest->setValidator(validator($plainRequest->all(), $plainRequest->rules()));

    expect($plainRequest->adminUrl())->toBe('http://sweet-bakes.kneadit.test/admin');
});

test('referralCode resolves session and cookie values', function () {
    $sessionRequest = StoreOnboardingRequest::create('/onboarding', 'POST', [], [], [], [
        'HTTP_COOKIE' => 'referral_code=COOKIEVAL',
    ]);
    $sessionRequest->setLaravelSession(app('session.store'));
    $sessionRequest->session()->put('referral_code', 'SESSIONVAL');

    expect($sessionRequest->referralCode())->toBe('SESSIONVAL');

    $sessionRequest->session()->forget('referral_code');

    $cookieRequest = StoreOnboardingRequest::create('/onboarding', 'POST', [], [
        'referral_code' => 'COOKIEVAL',
    ]);
    $cookieRequest->setLaravelSession(app('session.store'));

    expect($cookieRequest->referralCode())->toBe('COOKIEVAL');

    $emptyRequest = StoreOnboardingRequest::create('/onboarding', 'POST');
    $emptyRequest->setLaravelSession(app('session.store'));

    expect($emptyRequest->referralCode())->toBeNull();
});

function validOnboardingData(): array
{
    return [
        'store_name' => 'Sweet Bakes',
        'subdomain' => 'sweet-bakes-' . uniqid(),
        'storefront_choice' => 'kneadit',
    ];
}
