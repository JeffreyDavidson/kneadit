<?php

use App\Enums\Storefront\HeroStyle;
use App\Filament\Pages\Settings\HomepageBuilder;
use App\Http\Controllers\Tenant\Storefront\HomeController;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\withoutMiddleware;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('homepage builder renders and saves', function () {
    $component = livewire(HomepageBuilder::class);

    $component->assertOk();
    $component->call('save');

    expect(settings('homepage_sections'))->not->toBeNull();
});

test('reset to defaults action restores defaults', function () {
    resolve(SettingsManager::class)->setMany([
        'hero_tagline' => 'Custom tagline that should get overwritten',
    ]);

    livewire(HomepageBuilder::class)
        ->callAction('resetToDefaults');

    expect(settings('hero_tagline'))->toBe('Where every bite tells a story');
});

dataset('storefront hero image settings', [
    'homepage hero' => ['hero_image', 'heroImage'],
    'catering page' => ['catering_hero_image', 'cateringHeroImage'],
    'rewards page' => ['loyalty_hero_image', 'loyaltyHeroImage'],
    'gift cards page' => ['gift_cards_hero_image', 'giftCardsHeroImage'],
]);

test('uploaded hero images are stored on the public disk and saved as settings', function (string $setting, string $brandingProperty) {
    Storage::fake('public');

    livewire(HomepageBuilder::class)
        ->set("heroUploads.{$setting}", UploadedFile::fake()->image('hero.jpg', 1600, 900))
        ->call('save')
        ->assertHasNoErrors();

    $path = TenantSettings::resolve()->branding->{$brandingProperty};

    expect($path)->toBeString()->toStartWith('storefront-heroes/');
    Storage::disk('public')->assertExists($path);
})->with('storefront hero image settings');

test('the builder shows the hero upload fields, style select and current image preview', function () {
    settings(['hero_image' => 'storefront-heroes/current.jpg']);

    livewire(HomepageBuilder::class)
        ->assertSeeHtml('id="hero-upload-hero_image"')
        ->assertSeeHtml('id="hero-upload-catering_hero_image"')
        ->assertSeeHtml('id="hero-upload-loyalty_hero_image"')
        ->assertSeeHtml('id="hero-upload-gift_cards_hero_image"')
        ->assertSeeHtml('id="hero-style"')
        ->assertSeeHtml(Storage::url('storefront-heroes/current.jpg'));
});

test('saving without a new upload keeps the existing hero image', function () {
    Storage::fake('public');
    settings(['hero_image' => 'storefront-heroes/existing.jpg']);

    livewire(HomepageBuilder::class)
        ->call('save')
        ->assertHasNoErrors();

    expect(settings('hero_image'))->toBe('storefront-heroes/existing.jpg');
});

test('the storefront home page renders the uploaded hero image', function () {
    Storage::fake('public');
    Route::get('/storefront-home-test', HomeController::class);

    livewire(HomepageBuilder::class)
        ->set('heroUploads.hero_image', UploadedFile::fake()->image('hero.jpg', 1600, 900))
        ->call('save');

    $path = settings('hero_image');
    app()->forgetScopedInstances();

    withoutMiddleware(tenantMiddleware())
        ->get('/storefront-home-test')->assertOk()->assertSeeHtml(Storage::url($path));
});

test('the storefront home page renders the chosen hero style', function () {
    Route::get('/storefront-home-test', HomeController::class);

    livewire(HomepageBuilder::class)
        ->set('hero_style', HeroStyle::FullPhoto->value)
        ->call('save');

    app()->forgetScopedInstances();

    withoutMiddleware(tenantMiddleware())
        ->get('/storefront-home-test')
        ->assertOk()
        ->assertSee('Handcrafted with love');
});

test('hero style defaults to split and loads the saved value', function () {
    livewire(HomepageBuilder::class)->assertSet('hero_style', 'split');

    settings(['hero_style' => 'fullphoto']);
    app()->forgetScopedInstances();

    livewire(HomepageBuilder::class)->assertSet('hero_style', 'fullphoto');
});

test('a valid hero style is saved', function (HeroStyle $style) {
    livewire(HomepageBuilder::class)
        ->set('hero_style', $style->value)
        ->call('save')
        ->assertHasNoErrors();

    expect(TenantSettings::resolve()->branding->heroStyle)->toBe($style->value);
})->with(HeroStyle::cases());

test('an invalid hero style is rejected and not saved', function () {
    settings(['hero_style' => 'split']);

    livewire(HomepageBuilder::class)
        ->set('hero_style', 'parallax')
        ->call('save')
        ->assertHasErrors(['hero_style']);

    expect(settings('hero_style'))->toBe('split');
});

dataset('invalid hero uploads', [
    'not an image' => fn () => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'),
    'svg markup' => fn () => UploadedFile::fake()->createWithContent('hero.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
    'over the size limit' => fn () => UploadedFile::fake()->image('huge.jpg')->size(6000),
]);

test('invalid hero uploads are rejected and nothing is stored', function (UploadedFile $file) {
    Storage::fake('public');

    livewire(HomepageBuilder::class)
        ->set('heroUploads.hero_image', $file)
        ->call('save')
        ->assertHasErrors(['heroUploads.hero_image']);

    expect(settings('hero_image'))->toBeNull()
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
})->with('invalid hero uploads');

test('removing a hero image clears the setting and deletes the stored file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('storefront-heroes/old.jpg', 'image-bytes');
    settings(['hero_image' => 'storefront-heroes/old.jpg']);

    livewire(HomepageBuilder::class)
        ->call('removeHeroImage', 'hero_image');

    expect(settings('hero_image'))->toBeNull()
        ->and(TenantSettings::resolve()->branding->heroImageUrl())->toContain('unsplash.com');
    Storage::disk('public')->assertMissing('storefront-heroes/old.jpg');
});

test('removing a hero image never deletes files outside the hero directory', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/cake.jpg', 'image-bytes');
    settings(['hero_image' => 'products/cake.jpg']);

    livewire(HomepageBuilder::class)
        ->call('removeHeroImage', 'hero_image');

    expect(settings('hero_image'))->toBeNull();
    Storage::disk('public')->assertExists('products/cake.jpg');
});

test('removing an unknown setting key does nothing', function () {
    settings(['hero_image' => 'storefront-heroes/old.jpg', 'store_logo' => 'logos/logo.png']);

    livewire(HomepageBuilder::class)
        ->call('removeHeroImage', 'store_logo');

    expect(settings('store_logo'))->toBe('logos/logo.png')
        ->and(settings('hero_image'))->toBe('storefront-heroes/old.jpg');
});
