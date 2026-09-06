<?php

use App\Enums\Content\BlogPostCategory;
use App\Models\Content\BlogPost;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('blog feed returns xml', function () {
    URL::forceRootUrl('https://kneadit.test');
    URL::forceScheme('https');

    get(route('blog.feed'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSeeHtml('<link>https://kneadit.test/resources</link>')
        ->assertSeeHtml('href="https://kneadit.test/resources/feed.xml"');
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
        ->assertSeeHtml('<category>Baker Tips</category>');
});
