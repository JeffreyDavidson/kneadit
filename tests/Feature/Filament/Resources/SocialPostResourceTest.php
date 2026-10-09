<?php

use App\Enums\Marketing\SocialPlatform;
use App\Filament\Resources\SocialPosts\Pages\ListSocialPosts;
use App\Filament\Resources\SocialPosts\SocialPostResource;
use App\Models\Content\SocialPost;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list social posts in the table', function () {
    $posts = SocialPost::factory()->count(3)->create();

    livewire(ListSocialPosts::class)
        ->assertCanSeeTableRecords($posts);
});

test('can render social post table columns', function () {
    SocialPost::factory()->create();

    livewire(ListSocialPosts::class)
        ->assertCanRenderTableColumn('platform')
        ->assertCanRenderTableColumn('caption');
});

test('can search social posts by caption', function () {
    $target = SocialPost::factory()->create(['caption' => 'Fresh sourdough baked today']);
    $other = SocialPost::factory()->create(['caption' => 'Holiday special cookies']);

    livewire(ListSocialPosts::class)
        ->searchTable('sourdough')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can edit a social post via table action', function () {
    $post = SocialPost::factory()->create();

    livewire(ListSocialPosts::class)
        ->callAction(TestAction::make('edit')->table($post), data: [
            'platform' => $post->platform->value,
            'caption' => 'Updated caption for our bakery',
        ])
        ->assertHasNoFormErrors();

    expect($post->fresh()->caption)->toBe('Updated caption for our bakery');
});

test('a social post image is stored on the public disk even when the default disk is private', function () {
    config(['filesystems.default' => 'local']);
    Storage::fake('local');
    Storage::fake('public');

    livewire(ListSocialPosts::class)
        ->callAction(CreateAction::class, data: [
            'platform' => SocialPlatform::Instagram->value,
            'caption' => 'Fresh bread straight from the oven!',
            'image_path' => UploadedFile::fake()->image('loaf.jpg'),
        ])
        ->assertHasNoFormErrors();

    $path = SocialPost::query()->firstOrFail()->image_path;

    Storage::disk('public')->assertExists($path);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('can create a social post via slide-over', function () {
    livewire(ListSocialPosts::class)
        ->callAction(CreateAction::class, data: [
            'platform' => SocialPlatform::Instagram->value,
            'caption' => 'Fresh bread straight from the oven!',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(SocialPost::class, [
        'caption' => 'Fresh bread straight from the oven!',
    ]);
});

test('create social post validates required fields', function () {
    $cases = [
        [['platform' => null], ['platform' => 'required']],
        [['caption' => null], ['caption' => 'required']],
    ];

    foreach ($cases as [$data, $errors]) {
        livewire(ListSocialPosts::class)
            ->callAction(CreateAction::class, data: [
                'platform' => SocialPlatform::Instagram->value,
                'caption' => 'Test caption',
                ...$data,
            ])
            ->assertHasFormErrors($errors);
    }
});

test('can filter social posts by platform', function () {
    $instagram = SocialPost::factory()->create(['platform' => SocialPlatform::Instagram]);
    $facebook = SocialPost::factory()->create(['platform' => SocialPlatform::Facebook]);

    livewire(ListSocialPosts::class)
        ->filterTable('platform', SocialPlatform::Instagram->value)
        ->assertCanSeeTableRecords(collect([$instagram]))
        ->assertCanNotSeeTableRecords(collect([$facebook]));
});

test('navigation badge shows scheduled post count', function () {
    SocialPost::factory()->scheduled()->count(3)->create();
    SocialPost::factory()->draft()->create();

    expect(SocialPostResource::getNavigationBadge())
        ->toBe('3');
});

test('navigation badge returns null when no scheduled posts', function () {
    SocialPost::factory()->draft()->create();

    expect(SocialPostResource::getNavigationBadge())
        ->toBeNull();
});

test('resource returns globally searchable attributes', function () {
    expect(SocialPostResource::getGloballySearchableAttributes())
        ->toBe(['caption']);
});

test('resource returns global search result title', function () {
    $post = SocialPost::factory()->create(['caption' => 'Fresh bread from the oven today']);

    $title = SocialPostResource::getGlobalSearchResultTitle($post);

    expect($title)->toBeString();
});

test('resource returns global search result details', function () {
    $post = SocialPost::factory()->create();

    $details = SocialPostResource::getGlobalSearchResultDetails($post);

    expect($details)
        ->toHaveKeys(['Platform', 'Status']);
});

test('the scheduled time is entered and shown in the bakery timezone', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'Pacific/Auckland'])));

    livewire(ListSocialPosts::class)
        ->callAction(CreateAction::class, data: [
            'platform' => SocialPlatform::Instagram->value,
            'caption' => 'Fresh loaves at midnight',
            'status' => 'draft',
            'scheduled_for' => '2030-10-15 00:30:00',
        ])
        ->assertHasNoFormErrors();
    $post = SocialPost::query()->where('caption', 'Fresh loaves at midnight')->firstOrFail();

    // 00:30 on the 15th in Auckland (NZDT, UTC+13) is 11:30 on the 14th in UTC.
    expect($post->scheduled_for->format('Y-m-d H:i'))->toBe('2030-10-14 11:30');

    livewire(ListSocialPosts::class)
        ->mountAction(TestAction::make('edit')->table($post))
        ->assertSet('mountedActions.0.data.scheduled_for', '2030-10-15 00:30:00');
});
