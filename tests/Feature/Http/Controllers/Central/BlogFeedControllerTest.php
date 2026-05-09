<?php

use App\Enums\Content\BlogPostCategory;
use App\Models\Content\BlogPost;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('blog feed returns xml', function () {
    get(route('blog.feed'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

test('blog feed renders category enum labels', function () {
    BlogPost::factory()
        ->published()
        ->inCategory(BlogPostCategory::Tips)
        ->create([
            'title' => 'Cottage Food Pricing Basics',
            'slug' => 'cottage-food-pricing-basics',
        ]);

    get(route('blog.feed'))
        ->assertOk()
        ->assertSee('<category>Baker Tips</category>', false);
});
