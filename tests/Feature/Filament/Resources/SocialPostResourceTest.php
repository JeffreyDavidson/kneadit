<?php

use App\Enums\SocialPlatform;
use App\Filament\Resources\SocialPosts\Pages\ListSocialPosts;
use App\Models\SocialPost;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list social posts in the table', function () {
    $posts = SocialPost::factory()->count(3)->create();

    Livewire::test(ListSocialPosts::class)
        ->assertCanSeeTableRecords($posts);
});

test('can render social post table columns', function (string $column) {
    SocialPost::factory()->create();

    Livewire::test(ListSocialPosts::class)
        ->assertCanRenderTableColumn($column);
})->with(['platform', 'caption']);

test('can search social posts by caption', function () {
    $target = SocialPost::factory()->create(['caption' => 'Fresh sourdough baked today']);
    $other = SocialPost::factory()->create(['caption' => 'Holiday special cookies']);

    Livewire::test(ListSocialPosts::class)
        ->searchTable('sourdough')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can edit a social post via table action', function () {
    $post = SocialPost::factory()->create();

    Livewire::test(ListSocialPosts::class)
        ->callTableAction('edit', $post, data: [
            'platform' => $post->platform->value,
            'caption' => 'Updated caption for our bakery',
        ])
        ->assertHasNoTableActionErrors();

    expect($post->fresh()->caption)->toBe('Updated caption for our bakery');
});

test('can create a social post via slide-over', function () {
    Livewire::test(ListSocialPosts::class)
        ->callAction(CreateAction::class, data: [
            'platform' => SocialPlatform::Instagram->value,
            'caption' => 'Fresh bread straight from the oven!',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(SocialPost::class, [
        'caption' => 'Fresh bread straight from the oven!',
    ]);
});
