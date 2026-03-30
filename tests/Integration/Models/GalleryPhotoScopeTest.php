<?php

use App\Models\GalleryPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('visible scope returns only visible photos', function () {
    $visible = GalleryPhoto::factory()->create(['is_visible' => true]);
    GalleryPhoto::factory()->hidden()->create();

    $results = GalleryPhoto::query()->visible()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($visible->id);
});
