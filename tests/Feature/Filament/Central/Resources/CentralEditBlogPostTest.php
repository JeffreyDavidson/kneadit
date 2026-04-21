<?php

use App\Filament\Central\Resources\BlogPostResource\Pages\EditBlogPost;
use App\Models\Content\BlogPost;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('edit blog post page renders with existing record', function () {
    $id = DB::table('blog_posts')->insertGetId([
        'title' => 'Original Title',
        'slug' => 'original-title-' . uniqid(),
        'body' => 'Body content here.',
        'is_published' => true,
        'published_at' => now()->subDay(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    /** @var BlogPost $post */
    $post = BlogPost::query()->findOrFail($id);

    Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])
        ->assertOk();
});

test('edit blog post page exposes a delete header action', function () {
    $id = DB::table('blog_posts')->insertGetId([
        'title' => 'Deletable',
        'slug' => 'deletable-' . uniqid(),
        'body' => 'Body content here.',
        'is_published' => true,
        'published_at' => now()->subDay(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    /** @var BlogPost $post */
    $post = BlogPost::query()->findOrFail($id);

    Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])
        ->assertActionExists('delete');
});
