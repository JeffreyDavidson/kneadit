<?php

use App\Enums\Marketing\SocialPlatform;
use App\Filament\Resources\SocialPosts\Pages\ListSocialPosts;
use App\Models\Content\SocialPost;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
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
        ->callAction(TestAction::make('edit')->table($post), data: [
            'platform' => $post->platform->value,
            'caption' => 'Updated caption for our bakery',
        ])
        ->assertHasNoFormErrors();

    expect($post->refresh()->caption)->toBe('Updated caption for our bakery');
});

test('can create a social post via slide-over', function () {
    Livewire::test(ListSocialPosts::class)
        ->callAction(CreateAction::class, data: [
            'platform' => SocialPlatform::Instagram->value,
            'caption' => 'Fresh bread straight from the oven!',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(SocialPost::class, [
        'caption' => 'Fresh bread straight from the oven!',
    ]);
});

test('create social post validates required fields', function (array $data, array $errors) {
    Livewire::test(ListSocialPosts::class)
        ->callAction(CreateAction::class, data: [
            'platform' => SocialPlatform::Instagram->value,
            'caption' => 'Test caption',
            ...$data,
        ])
        ->assertHasFormErrors($errors);
})->with([
    'platform is required' => [['platform' => null], ['platform' => 'required']],
    'caption is required' => [['caption' => null], ['caption' => 'required']],
]);

test('can filter social posts by platform', function () {
    $instagram = SocialPost::factory()->create(['platform' => SocialPlatform::Instagram]);
    $facebook = SocialPost::factory()->create(['platform' => SocialPlatform::Facebook]);

    Livewire::test(ListSocialPosts::class)
        ->filterTable('platform', SocialPlatform::Instagram->value)
        ->assertCanSeeTableRecords(collect([$instagram]))
        ->assertCanNotSeeTableRecords(collect([$facebook]));
});

test('navigation badge shows scheduled post count', function () {
    SocialPost::factory()->scheduled()->count(3)->create();
    SocialPost::factory()->draft()->create();

    expect(App\Filament\Resources\SocialPosts\SocialPostResource::getNavigationBadge())
        ->toBe('3');
});

test('navigation badge returns null when no scheduled posts', function () {
    SocialPost::factory()->draft()->create();

    expect(App\Filament\Resources\SocialPosts\SocialPostResource::getNavigationBadge())
        ->toBeNull();
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\SocialPosts\SocialPostResource::getGloballySearchableAttributes())
        ->toBe(['caption']);
});

test('resource returns global search result title', function () {
    $post = SocialPost::factory()->create(['caption' => 'Fresh bread from the oven today']);

    $title = App\Filament\Resources\SocialPosts\SocialPostResource::getGlobalSearchResultTitle($post);

    expect($title)->toBeString();
});

test('resource returns global search result details', function () {
    $post = SocialPost::factory()->create();

    $details = App\Filament\Resources\SocialPosts\SocialPostResource::getGlobalSearchResultDetails($post);

    expect($details)
        ->toHaveKeys(['Platform', 'Status']);
});
