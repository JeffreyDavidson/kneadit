<?php

use App\Filament\Pages\Settings\HomepageBuilder;
use App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos;
use App\Models\Content\GalleryPhoto;
use App\Models\Content\TenantBlogPost;
use App\Models\Customers\CustomerPhoto;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Laravel\Pennant\Feature;

use function Pest\Laravel\withoutMiddleware;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    // Production's default disk is private, and only the public disk has a browser-reachable URL.
    config([
        'filesystems.default' => 'local',
        'filesystems.disks.public.url' => 'https://public-disk.test/storage',
    ]);
});

test('blog index and post pages render featured images from the public disk', function () {
    TenantBlogPost::factory()->published()->create(['featured_image' => 'blog-images/related.jpg']);
    $post = TenantBlogPost::factory()->published()->create(['featured_image' => 'blog-images/post.jpg']);

    $index = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog', [], false));
    $show = withoutMiddleware(tenantMiddleware())->get(route('storefront.blog.show', ['post' => $post->slug], false));

    $index->assertOk()->assertSeeHtml('https://public-disk.test/storage/blog-images/post.jpg')->assertSeeHtml('https://public-disk.test/storage/blog-images/related.jpg');
    $show->assertOk()->assertSeeHtml('https://public-disk.test/storage/blog-images/post.jpg')->assertSeeHtml('https://public-disk.test/storage/blog-images/related.jpg');
});

test('the biscotto about page renders the store photo from the public disk', function () {
    settings(['storefront_theme' => 'biscotto', 'store_photo' => 'tenants/bakery/portrait.jpg', 'about_us_text' => 'Baked fresh daily.']);

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.about', [], false));

    $response->assertOk()->assertSeeHtml('https://public-disk.test/storage/tenants/bakery/portrait.jpg');
});

test('the homepage about section renders the store photo from the public disk', function () {
    settings(['store_photo' => 'tenants/bakery/portrait.jpg', 'about_us_text' => 'Baked fresh daily.']);

    $html = Blade::render('<x-storefront.home.about />');

    expect($html)->toContain('https://public-disk.test/storage/tenants/bakery/portrait.jpg');
});

test('the catering page renders past event photos from the public disk', function () {
    CustomerPhoto::factory()->approved()->create([
        'caption' => 'Catering for a wedding',
        'photo_path' => 'customer-photos/wedding.jpg',
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.catering', [], false));

    $response->assertOk()->assertSeeHtml('https://public-disk.test/storage/customer-photos/wedding.jpg');
});

test('every branding hero image resolves against the public disk', function () {
    settings([
        'hero_image' => 'storefront-heroes/home.jpg',
        'catering_hero_image' => 'storefront-heroes/catering.jpg',
        'loyalty_hero_image' => 'storefront-heroes/loyalty.jpg',
        'gift_cards_hero_image' => 'storefront-heroes/gift-cards.jpg',
    ]);

    $branding = resolve(TenantSettings::class)->branding;

    expect($branding->heroImageUrl())->toBe('https://public-disk.test/storage/storefront-heroes/home.jpg')
        ->and($branding->cateringHeroImageUrl())->toBe('https://public-disk.test/storage/storefront-heroes/catering.jpg')
        ->and($branding->loyaltyHeroImageUrl())->toBe('https://public-disk.test/storage/storefront-heroes/loyalty.jpg')
        ->and($branding->giftCardsHeroImageUrl())->toBe('https://public-disk.test/storage/storefront-heroes/gift-cards.jpg');
});

test('the homepage call to action renders the hero image from the public disk', function () {
    settings(['hero_image' => 'storefront-heroes/home.jpg']);

    $html = Blade::render('<x-storefront.home.cta :config="[]" />');

    expect($html)->toContain('https://public-disk.test/storage/storefront-heroes/home.jpg');
});

test('the homepage builder previews a stored hero image from the public disk', function () {
    test()->actingAs(User::factory()->owner()->create());
    settings(['hero_image' => 'storefront-heroes/home.jpg']);

    livewire(HomepageBuilder::class)
        ->assertSeeHtml('https://public-disk.test/storage/storefront-heroes/home.jpg');
});

test('the gallery photos table reads images from the public disk', function () {
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    GalleryPhoto::factory()->create();

    livewire(ListGalleryPhotos::class)
        ->assertTableColumnExists(
            'image_path',
            fn (ImageColumn $column): bool => $column->getDiskName() === 'public',
        );
});
