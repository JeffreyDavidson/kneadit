<?php

use App\Models\Content\GalleryPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('visible scope returns only visible photos', function () {
    $visible = GalleryPhoto::factory()->visible()->create();
    GalleryPhoto::factory()->hidden()->create();

    $results = GalleryPhoto::query()->visible()->get();

    expect($results)->toHaveCount(1)
        ->and($results->firstOrFail()->id)->toBe($visible->id);
});
