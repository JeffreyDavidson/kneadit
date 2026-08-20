<?php

use App\Enums\Content\BlogPostCategory;
use App\Models\Content\BlogPost;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    DB::connection('central')->setPdo(
        DB::connection('sqlite')->getPdo(),
    );

    if (! Schema::hasColumn('blog_posts', 'category')) {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('category')->default('guides');
        });
    }
});

test('published scope returns only published posts with past published_at', function () {
    BlogPost::factory()->published()->create();
    BlogPost::factory()->draft()->create();
    BlogPost::factory()->create([
        'is_published' => true,
        'published_at' => now()->addDay(),
    ]);

    $results = BlogPost::query()->published()->get();

    expect($results)->toHaveCount(1);
});

test('published scope excludes posts with null published_at', function () {
    BlogPost::factory()->create([
        'is_published' => true,
        'published_at' => null,
    ]);

    $results = BlogPost::query()->published()->get();

    expect($results)->toBeEmpty();
});

test('inCategory filters posts by category', function () {
    BlogPost::factory()->inCategory(BlogPostCategory::Tips)->create();
    BlogPost::factory()->inCategory(BlogPostCategory::News)->create();

    $results = BlogPost::query()->inCategory(BlogPostCategory::Tips)->get();

    expect($results)->toHaveCount(1);
});

test('relatedTo returns posts in the same category excluding the given post', function () {
    $post = BlogPost::factory()->inCategory(BlogPostCategory::Guides)->create();
    $related = BlogPost::factory()->inCategory(BlogPostCategory::Guides)->create();
    BlogPost::factory()->inCategory(BlogPostCategory::News)->create();

    $results = BlogPost::query()->relatedTo($post)->get();

    expect($results)->toHaveCount(1)
        ->and($results->firstOrFail()->id)->toBe($related->id);
});

test('relatedTo respects the limit parameter', function () {
    $post = BlogPost::factory()->inCategory(BlogPostCategory::Tips)->create();
    BlogPost::factory()->inCategory(BlogPostCategory::Tips)->count(5)->create();

    $results = BlogPost::query()->relatedTo($post, 2)->get();

    expect($results)->toHaveCount(2);
});

test('forListing returns published posts ordered by published_at descending', function () {
    $older = BlogPost::factory()->published()->create([
        'published_at' => now()->subDays(5),
    ]);
    $newer = BlogPost::factory()->published()->create([
        'published_at' => now()->subDay(),
    ]);
    BlogPost::factory()->draft()->create();

    $results = BlogPost::query()->forListing()->get();

    $last = $results->last();
    throw_unless($last instanceof BlogPost, RuntimeException::class, 'Expected a second listing result.');

    expect($results)->toHaveCount(2)
        ->and($results->firstOrFail()->id)->toBe($newer->id)
        ->and($last->id)->toBe($older->id);
});

test('forListing filters by category when provided', function () {
    BlogPost::factory()->published()->inCategory(BlogPostCategory::Tips)->create();
    BlogPost::factory()->published()->inCategory(BlogPostCategory::News)->create();

    $results = BlogPost::query()->forListing('tips')->get();

    expect($results)->toHaveCount(1);
});

test('forListing ignores category filter when set to all', function () {
    BlogPost::factory()->published()->inCategory(BlogPostCategory::Tips)->create();
    BlogPost::factory()->published()->inCategory(BlogPostCategory::News)->create();

    $results = BlogPost::query()->forListing('all')->get();

    expect($results)->toHaveCount(2);
});

test('forListing ignores invalid category string', function () {
    BlogPost::factory()->published()->inCategory(BlogPostCategory::Tips)->create();
    BlogPost::factory()->published()->inCategory(BlogPostCategory::News)->create();

    $results = BlogPost::query()->forListing('nonexistent')->get();

    expect($results)->toHaveCount(2);
});
