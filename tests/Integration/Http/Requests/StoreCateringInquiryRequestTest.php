<?php

use App\Http\Requests\Storefront\StoreCateringInquiryRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('catering inquiry requires essential fields', function () {
    foreach (['customer_name', 'customer_email', 'event_type', 'event_date', 'guest_count', 'details'] as $field) {
        $request = new StoreCateringInquiryRequest;
        $validator = validator([], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has($field))->toBeTrue();
    }
});

test('event_type must match a configured event type', function () {
    settings(['catering_event_types' => json_encode(['Kids Party', 'School Function'])]);
    resolve(App\Services\Settings\TenantSettingsRegistry::class)->flush();

    $request = new StoreCateringInquiryRequest;
    $validator = validator(['event_type' => 'Wedding'], $request->rules());

    expect($validator->errors()->has('event_type'))->toBeTrue();
});

test('event_type accepts a configured event type', function () {
    settings(['catering_event_types' => json_encode(['Kids Party', 'School Function'])]);
    resolve(App\Services\Settings\TenantSettingsRegistry::class)->flush();

    $request = new StoreCateringInquiryRequest;
    $validator = validator(['event_type' => 'Kids Party'], $request->rules());

    expect($validator->errors()->has('event_type'))->toBeFalse();
});
