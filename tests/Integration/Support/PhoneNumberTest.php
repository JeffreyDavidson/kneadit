<?php

use App\Support\PhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('the bakery country is the country of its store phone', function (string $storePhone, string $country) {
    settings(['store_phone' => $storePhone]);

    expect(PhoneNumber::homeCountry())->toBe($country);
})->with([
    'US store phone' => ['+19133877359', 'US'],
    'UK store phone' => ['+442079460958', 'GB'],
    'store phone without a country code' => ['555-0100', 'US'],
    'no store phone' => ['', 'US'],
]);

test('a UK bakery reads numbers without a country code as UK numbers and shows them nationally', function () {
    settings(['store_phone' => '+442079460958']);

    $stored = PhoneNumber::normalize('020 7946 0958');

    expect($stored)->toBe('+442079460958')
        ->and(PhoneNumber::display($stored))->toBe('020 7946 0958')
        ->and(PhoneNumber::display('+19133877359'))->toBe('+1 913-387-7359');
});
