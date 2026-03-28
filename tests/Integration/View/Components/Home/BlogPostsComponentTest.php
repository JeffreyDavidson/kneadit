<?php

use App\Models\BlogPost;
use App\View\Components\Home\BlogPosts;

beforeEach(fn () => setUpCentralTest());

test('loads published blog posts', function () {
    BlogPost::factory()->published()->count(2)->create();
    BlogPost::factory()->create();

    $component = new BlogPosts;

    expect($component->latestPosts)->toHaveCount(2)
        ->and($component->title)->toBe('From the Kitchen');
});
