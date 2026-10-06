<?php

use Illuminate\Support\Facades\Blade;

test('the tenant admin card is a themed surface, not a white box', function () {
    $html = Blade::render('<x-tenant-admin.card title="Product Trends">Body</x-tenant-admin.card>');

    expect($html)
        ->toContain('Product Trends')
        ->toContain('Body')
        ->not->toContain('bg-white')
        ->not->toContain('text-white');
});
