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

test('blog index metadata uses application URLs', function () {
    URL::forceRootUrl('https://kneadit.test');
    URL::forceScheme('https');

    get(route('blog.index'))
        ->assertOk()
        ->assertSeeHtml('<meta property="og:image" content="https://kneadit.test/og.svg" />')
        ->assertSeeHtml('href="https://kneadit.test/resources/feed.xml"')
        ->assertDontSee('https://getkneadit.app/og.svg');
});

test('blog header navigation uses application routes', function () {
    URL::forceRootUrl('https://kneadit.test');
    URL::forceScheme('https');

    get(route('blog.index'))
        ->assertOk()
        ->assertSeeHtml('<a href="https://kneadit.test" class="nav-brand">KneadIt</a>')
        ->assertSeeHtml('<a href="https://kneadit.test">Home</a>')
        ->assertSeeHtml('<a href="https://kneadit.test#features">Features</a>')
        ->assertSeeHtml('<a href="https://kneadit.test#pricing">Pricing</a>')
        ->assertSeeHtml('<a href="https://kneadit.test#contact">Contact</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/resources">Resources</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/register" class="nav-cta">Get Started</a>');
});

test('blog footer navigation uses application routes', function () {
    URL::forceRootUrl('https://kneadit.test');
    URL::forceScheme('https');

    get(route('blog.index'))
        ->assertOk()
        ->assertSeeHtml('<a href="https://kneadit.test/terms">Terms</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/privacy">Privacy</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/resources">Resources</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/changelog">Changelog</a>');
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
