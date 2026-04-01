<?php

use App\Enums\Content\GalleryCategory;
use App\Models\Content\GalleryPhoto;

beforeEach(fn () => setUpTenantTest());

it('casts category to GalleryCategory enum', function () {
    $photo = GalleryPhoto::factory()->create([
        'category' => 'products',
    ]);

    $photo->refresh();

    expect($photo->category)->toBeInstanceOf(GalleryCategory::class)
        ->and($photo->category)->toBe(GalleryCategory::Products);
});
