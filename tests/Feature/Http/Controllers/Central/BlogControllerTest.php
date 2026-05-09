<?php

use App\Enums\Content\BlogPostCategory;
use App\Models\Content\BlogPost;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('blog index page renders', function () {
    get(route('blog.index'))->assertOk();
});

test('index validates category against allowed values', function () {
    get(route('blog.index', ['category' => 'malicious-value']))
        ->assertRedirect();
});

test('blog show ignores related published posts without routable slugs', function () {
    $post = BlogPost::factory()
        ->published()
        ->inCategory(BlogPostCategory::Guides)
        ->create(['slug' => 'valid-resource']);

    BlogPost::factory()
        ->published()
        ->inCategory(BlogPostCategory::Guides)
        ->create(['slug' => '']);

    get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertDontSee('Missing required parameter');
});
