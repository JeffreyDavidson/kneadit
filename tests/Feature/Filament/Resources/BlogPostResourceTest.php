<?php

use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Models\Content\TenantBlogPost;
use App\Models\Staff\User;
use Livewire\Livewire;

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can render blog posts list page', function () {
    Livewire::test(ListBlogPosts::class)
        ->assertOk();
});

test('can render blog post create page', function () {
    Livewire::test(CreateBlogPost::class)
        ->assertOk();
});

test('can create a blog post', function () {
    Livewire::test(CreateBlogPost::class)
        ->fillForm([
            'title' => 'Our New Sourdough Recipe',
            'slug' => 'our-new-sourdough-recipe',
            'body' => '<p>We are excited to share our new recipe!</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(TenantBlogPost::class, [
        'title' => 'Our New Sourdough Recipe',
        'slug' => 'our-new-sourdough-recipe',
    ]);
});

test('can edit a blog post', function () {
    $post = TenantBlogPost::factory()->create();

    Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Title',
            'slug' => $post->slug,
            'body' => $post->body,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh()->title)->toBe('Updated Title');
});

test('create blog post validates required fields', function (array $data, array $errors) {
    Livewire::test(CreateBlogPost::class)
        ->fillForm([
            'title' => 'Test',
            'slug' => 'test',
            'body' => '<p>Content</p>',
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    'title is required' => [['title' => null], ['title' => 'required']],
    'slug is required' => [['slug' => null], ['slug' => 'required']],
]);

test('can list blog posts in the table', function () {
    $posts = TenantBlogPost::factory()->count(3)->create();

    Livewire::test(ListBlogPosts::class)
        ->assertCanSeeTableRecords($posts);
});

test('can render blog post table columns', function (string $column) {
    TenantBlogPost::factory()->create();

    Livewire::test(ListBlogPosts::class)
        ->assertCanRenderTableColumn($column);
})->with(['title', 'is_published', 'published_at']);

test('can search blog posts by title', function () {
    $target = TenantBlogPost::factory()->create(['title' => 'Sourdough Tips']);
    $other = TenantBlogPost::factory()->create(['title' => 'Cookie Recipes']);

    Livewire::test(ListBlogPosts::class)
        ->searchTable('Sourdough')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can sort blog posts by title', function () {
    $alpha = TenantBlogPost::factory()->create(['title' => 'Alpha Post']);
    $zeta = TenantBlogPost::factory()->create(['title' => 'Zeta Post']);

    Livewire::test(ListBlogPosts::class)
        ->sortTable('title')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('title', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});

test('can filter blog posts by published status', function () {
    $published = TenantBlogPost::factory()->published()->create();
    $draft = TenantBlogPost::factory()->create();

    Livewire::test(ListBlogPosts::class)
        ->filterTable('is_published', '1')
        ->assertCanSeeTableRecords(collect([$published]))
        ->assertCanNotSeeTableRecords(collect([$draft]));
});

test('resource returns globally searchable attributes', function () {
    expect(BlogPostResource::getGloballySearchableAttributes())
        ->toBe(['title', 'excerpt']);
});

test('resource returns global search result title', function () {
    $post = TenantBlogPost::factory()->create(['title' => 'Bread Making 101']);

    expect(BlogPostResource::getGlobalSearchResultTitle($post))
        ->toBe('Bread Making 101');
});

test('resource returns global search result details', function () {
    $post = TenantBlogPost::factory()->create([
        'author_name' => 'Chef Baker',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $details = BlogPostResource::getGlobalSearchResultDetails($post);

    expect($details)
        ->toHaveKey('Author', 'Chef Baker')
        ->toHaveKey('Published');
});
