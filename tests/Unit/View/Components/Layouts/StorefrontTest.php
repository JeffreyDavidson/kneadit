<?php

use App\View\Components\Layouts\Storefront;

test('storefront metadata props default to null for layout fallbacks', function () {
    $component = new Storefront;

    expect($component->title)->toBeNull()
        ->and($component->metaDescription)->toBeNull();
});
