<?php

use App\Services\Platform\ChangelogService;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => setUpCentralTest());

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
        ->and($entries->first()['version'])->toBe('1.5.0')
        ->and($entries->first()['title'])->toBe('Blog & Platform Polish')
        ->and($entries->first()['items'])->toHaveCount(3);
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
        ->and($entries->first()['title'])->toBe('Published Release');
});
