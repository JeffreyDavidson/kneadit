<?php

use App\Filament\Resources\SocialPosts\Pages\ListSocialPosts;
use App\Models\SocialPost;
use App\Models\User;
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
