<?php

use App\Enums\Content\GalleryCategory;
use App\Http\Resources\GalleryPhotoResource;
use App\Models\Content\GalleryPhoto;
use App\Models\Staff\User;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('projects a gallery photo into the JSON:API shape', function () {
    $photo = GalleryPhoto::factory()->create([
        'title' => 'Birthday Cake',
        'image_path' => 'photos/cake.jpg',
        'category' => GalleryCategory::Products,
    ]);

    $resource = new GalleryPhotoResource($photo);
    $request = request();

    expect($resource->toType($request))->toBe('gallery-photos')
        ->and($resource->toId($request))->toBe((string) $photo->id);

    $attributes = $resource->toAttributes($request);

    expect($attributes)
        ->toHaveKeys(['title', 'image_path', 'category'])
        ->toMatchArray([
            'title' => 'Birthday Cake',
            'image_path' => 'photos/cake.jpg',
            'category' => GalleryCategory::Products->value,
        ]);
});
