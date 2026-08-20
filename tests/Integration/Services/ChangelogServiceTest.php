<?php

use App\Services\Platform\ChangelogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    setUpCentralTest();
    Cache::flush();
});

test('returns entries from GitHub releases API', function () {
    Http::fake([
        'api.github.com/repos/*' => Http::response([
            [
                'tag_name' => 'v1.5.0',
                'name' => 'Blog & Platform Polish',
                'published_at' => '2026-03-12T00:00:00Z',
                'draft' => false,
                'body' => "- 10 SEO-optimized articles\n- Blog post auto-slug generation\n- 374 tests passing",
            ],
        ]),
    ]);

    $entries = resolve(ChangelogService::class)->entries();

    expect($entries)->toHaveCount(1)
        ->and($entries->firstOrFail()['version'])->toBe('1.5.0')
        ->and($entries->firstOrFail()['title'])->toBe('Blog & Platform Polish')
        ->and($entries->firstOrFail()['items'])->toHaveCount(3);
});

test('falls back to config when GitHub API fails', function () {
    Http::fake([
        'api.github.com/*' => Http::response(null, 500),
    ]);

    $entries = resolve(ChangelogService::class)->entries();

    expect($entries)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('filters out draft releases', function () {
    Http::fake([
        'api.github.com/repos/*' => Http::response([
            [
                'tag_name' => 'v1.6.0',
                'name' => 'Draft Release',
                'published_at' => '2026-04-01T00:00:00Z',
                'draft' => true,
                'body' => '- Something new',
            ],
            [
                'tag_name' => 'v1.5.0',
                'name' => 'Published Release',
                'published_at' => '2026-03-12T00:00:00Z',
                'draft' => false,
                'body' => '- Something shipped',
            ],
        ]),
    ]);

    $entries = resolve(ChangelogService::class)->entries();

    expect($entries)->toHaveCount(1)
        ->and($entries->firstOrFail()['title'])->toBe('Published Release');
});

test('falls back to config when GitHub API throws exception', function () {
    Http::fake([
        'api.github.com/*' => fn () => throw new RuntimeException('Connection refused'),
    ]);

    config(['changelog' => [
        ['date' => '2026-01-01', 'version' => '1.0.0', 'title' => 'Fallback', 'items' => ['Initial release']],
    ]]);

    $entries = resolve(ChangelogService::class)->entries();

    expect($entries)->toHaveCount(1)
        ->and($entries->firstOrFail()['title'])->toBe('Fallback');
});

test('parses empty body into empty items array', function () {
    Http::fake([
        'api.github.com/repos/*' => Http::response([
            [
                'tag_name' => 'v1.0.0',
                'name' => 'Empty Release',
                'published_at' => '2026-01-01T00:00:00Z',
                'draft' => false,
                'body' => '',
            ],
        ]),
    ]);

    $entries = resolve(ChangelogService::class)->entries();

    expect($entries->firstOrFail()['items'])->toBeEmpty();
});

test('cached payload survives unserialize with allowed_classes=false (the prod cache config)', function () {
    Http::fake([
        'api.github.com/repos/*' => Http::response([
            [
                'tag_name' => 'v1.0.0',
                'name' => 'Release',
                'published_at' => '2026-01-01T00:00:00Z',
                'draft' => false,
                'body' => '- something',
            ],
        ]),
    ]);

    Cache::forget('changelog_entries');
    resolve(ChangelogService::class)->entries();

    // The cache stores either the raw payload or Cache::flexible's wrapper. Whichever
    // it is, it must serialize to a class-free shape that unserialize can rehydrate
    // when allowed_classes is false (config('cache.serializable_classes') === false).
    $cached = Cache::get('changelog_entries');
    $rehydrated = unserialize(serialize($cached), ['allowed_classes' => false]);

    expect($rehydrated)->not->toBeInstanceOf(__PHP_Incomplete_Class::class);
});

test('parses body with asterisk bullet points', function () {
    Http::fake([
        'api.github.com/repos/*' => Http::response([
            [
                'tag_name' => 'v1.0.0',
                'name' => 'Release',
                'published_at' => '2026-01-01T00:00:00Z',
                'draft' => false,
                'body' => "* First item\n* Second item\nNot a bullet",
            ],
        ]),
    ]);

    $entries = resolve(ChangelogService::class)->entries();

    expect($entries->firstOrFail()['items'])->toHaveCount(2);
});
