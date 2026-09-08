<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('renders the store name and configured social link', function () {
    settings([
        'store_name' => 'Sweet Dreams Bakery',
        'social_media_links' => json_encode(['instagram' => 'https://www.instagram.com/sweetdreams']),
    ]);

    $html = Blade::render('<x-home.social />');

    expect($html)->toContain('Follow Sweet Dreams Bakery')
        ->toContain('href="https://www.instagram.com/sweetdreams"');
});

test('omits the section when social links are empty', function () {
    settings(['social_media_links' => json_encode(['instagram' => ''])]);

    $html = Blade::render('<x-home.social />');

    expect(trim($html))->toBe('');
});
