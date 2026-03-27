<?php

use App\Http\Requests\StoreCateringInquiryRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('catering inquiry requires essential fields', function (string $field) {
    $request = new StoreCateringInquiryRequest;
    $validator = validator([], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has($field))->toBeTrue();
})->with(['customer_name', 'customer_email', 'event_type', 'event_date', 'guest_count', 'details']);
