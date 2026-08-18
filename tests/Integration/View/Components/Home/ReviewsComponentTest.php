<?php

use App\Models\Engagement\Review;
use App\View\Components\Home\Reviews;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('loads approved reviews for display', function () {
    Review::factory()->approved()->count(3)->create();
    Review::factory()->create();

    $component = new Reviews;

    expect($component->reviews)->toHaveCount(3);
});
