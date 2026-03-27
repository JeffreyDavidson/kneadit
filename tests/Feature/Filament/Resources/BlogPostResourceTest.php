<?php

use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Models\BlogPost;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->owner()->create());
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

    $this->assertDatabaseHas(BlogPost::class, [
        'title' => 'Our New Sourdough Recipe',
        'slug' => 'our-new-sourdough-recipe',
    ]);
});

test('can edit a blog post', function () {
    $post = BlogPost::factory()->create();

    Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Title',
            'slug' => $post->slug,
            'body' => $post->body,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->title)->toBe('Updated Title');
});
