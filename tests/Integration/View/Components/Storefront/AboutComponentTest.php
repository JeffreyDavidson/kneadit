<?php

use App\View\Components\Storefront\Home\About;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('loads the about section settings', function () {
    settings([
        'about_us_text' => 'We bake everything from scratch.',
        'store_name' => 'Sweet Dreams Bakery',
        'store_photo' => 'storefront/bakery.jpg',
    ]);

    $component = new About;

    expect($component->aboutUs)->toBe('We bake everything from scratch.')
        ->and($component->storeName)->toBe('Sweet Dreams Bakery')
        ->and($component->storePhoto)->toBe('storefront/bakery.jpg');
});
