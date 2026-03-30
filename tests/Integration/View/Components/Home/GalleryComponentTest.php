<?php

use App\Models\CustomerPhoto;
use App\View\Components\Home\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('loads approved and featured customer photos', function () {
    CustomerPhoto::factory()->count(3)->approved()->featured()->create();
    CustomerPhoto::factory()->approved()->create();
    CustomerPhoto::factory()->create();

    $component = new Gallery;

    expect($component->customerPhotos)->toHaveCount(3);
});
