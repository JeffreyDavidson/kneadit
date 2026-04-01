<?php

use App\Enums\GalleryCategory;
use App\Http\Resources\GalleryPhotoResource;
use App\Models\GalleryPhoto;
use App\Models\User;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('transforms a gallery photo into the expected API shape', function () {
    $photo = GalleryPhoto::factory()->create([
        'title' => 'Birthday Cake',
        'image_path' => 'photos/cake.jpg',
        'category' => GalleryCategory::Products,
    ]);

    $resource = new GalleryPhotoResource($photo);
    $data = $resource->toArray(request());

    expect($data)
        ->toHaveKeys(['id', 'title', 'image_path', 'category'])
        ->toMatchArray([
            'title' => 'Birthday Cake',
            'image_path' => 'photos/cake.jpg',
            'category' => GalleryCategory::Products,
        ]);
});
