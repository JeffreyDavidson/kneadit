<?php

use App\View\Composers\StorefrontComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('composes storefront view data with settings', function () {
    settings(['store_name' => 'Sweet Dreams Bakery']);

    $view = Mockery::mock(Illuminate\View\View::class);
    $view->shouldReceive('with')->once()->with(Mockery::on(function ($data) {
        return $data['ogStoreName'] === 'Sweet Dreams Bakery'
            && str_contains($data['ogDescription'], 'Sweet Dreams Bakery')
            && $data['storefrontTheme'] === 'classic';
    }));

    $composer = new StorefrontComposer;
    $composer->compose($view);
});
