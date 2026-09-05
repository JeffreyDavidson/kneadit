<?php

use App\Enums\Content\BlogPostCategory;
use App\Models\Content\BlogPost;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('blog index page renders', function () {
    get(route('blog.index'))->assertOk();
});

test('blog index metadata uses the application asset URL', function () {
    URL::forceRootUrl('https://kneadit.test');
    URL::forceScheme('https');

    get(route('blog.index'))
        ->assertOk()
        ->assertSeeHtml('<meta property="og:image" content="https://kneadit.test/og.svg" />')
        ->assertDontSee('https://getkneadit.app/og.svg');
});

test('index validates category against allowed values', function () {
    get(route('blog.index', ['category' => 'malicious-value']))
        ->assertRedirect();
});

test('blog show renders a central post by slug', function () {
    $post = BlogPost::factory()
        ->published()
        ->create([
            'title' => 'Central Resource Article',
            'slug' => 'central-resource-article',
        ]);

    get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('Central Resource Article');
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
