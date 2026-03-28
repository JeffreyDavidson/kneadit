<?php

use App\Models\CustomerPhoto;
use App\View\Components\Home\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('loads approved and featured customer photos', function () {
    CustomerPhoto::factory()->count(3)->create(['is_approved' => true, 'is_featured' => true]);
    CustomerPhoto::factory()->create(['is_approved' => true, 'is_featured' => false]);
    CustomerPhoto::factory()->create(['is_approved' => false]);

    $component = new Gallery;

    expect($component->customerPhotos)->toHaveCount(3);
});
