<?php

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    DB::connection('central')->setPdo(
        DB::connection('sqlite')->getPdo(),
    );
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

test('slug is auto generated from title', function () {
    $post = BlogPost::query()->create([
        'title' => 'My Awesome Blog Post',
        'body' => 'Some content here',
    ]);

    expect($post->slug)->toBe('my-awesome-blog-post');
});

test('slug is updated when title changes', function () {
    $post = BlogPost::query()->create([
        'title' => 'Original Title',
        'body' => 'Content',
    ]);

    $post->update(['title' => 'Updated Title']);

    expect($post->fresh()->slug)->toBe('updated-title');
});

test('duplicate slugs are made unique', function () {
    BlogPost::query()->create(['title' => 'Same Title', 'body' => 'First']);
    $post2 = BlogPost::query()->create(['title' => 'Same Title', 'body' => 'Second']);

    expect($post2->slug)->toBe('same-title-2');
});

test('published at can be set', function () {
    $post = BlogPost::query()->create([
        'title' => 'Published Post',
        'body' => 'Content',
        'is_published' => true,
        'published_at' => now(),
    ]);

    expect($post->is_published)->toBeTrue();
    expect($post->published_at)->not->toBeNull();
});

// tags_are_cast_to_array removed — blog_posts table doesn't have a tags column yet
