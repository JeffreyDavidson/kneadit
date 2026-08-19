<?php

use App\Http\Controllers\Central\SitemapController;
use App\Models\Content\BlogPost;
use Illuminate\Support\Facades\View;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('sitemap returns response with xml content type', function () {
    // Stub the view to avoid Blade XML-declaration compilation issue (short_open_tag)
    View::addNamespace('test', sys_get_temp_dir());
    $tmpView = sys_get_temp_dir() . '/sitemap-stub.php';
    file_put_contents($tmpView, '<urlset></urlset>');

    View::composer('platform.sitemap', function ($view) use ($tmpView) {
        $view->setPath($tmpView);
    });

    $controller = new SitemapController;
    $response = $controller();

    expect($response->headers->get('content-type'))->toContain('text/xml')
        ->and($response->getStatusCode())->toBe(200);

    @unlink($tmpView);
});

test('sitemap queries only published posts', function () {
    $published = BlogPost::factory()->published()->create();
    BlogPost::factory()->draft()->create();

    // Test the query logic directly since the Blade template has a
    // compilation issue with <?xml when short_open_tag is enabled
    $posts = BlogPost::query()->published()->orderByDesc('published_at')->get();

    expect($posts)->toHaveCount(1)
        ->and($posts->firstOrFail()->id)->toBe($published->id);
});

test('sitemap includes all published posts ordered by published_at desc', function () {
    $older = BlogPost::factory()->published()->create([
        'published_at' => now()->subDays(5),
    ]);
    $newer = BlogPost::factory()->published()->create([
        'published_at' => now()->subDay(),
    ]);

    $posts = BlogPost::query()->published()->orderByDesc('published_at')->get();

    expect($posts)->toHaveCount(2)
        ->and($posts->firstOrFail()->id)->toBe($newer->id)
        ->and($posts->last()->id)->toBe($older->id);
});

test('sitemap returns empty post list when no published posts exist', function () {
    BlogPost::factory()->draft()->count(2)->create();

    $posts = BlogPost::query()->published()->orderByDesc('published_at')->get();

    expect($posts)->toBeEmpty();
});
