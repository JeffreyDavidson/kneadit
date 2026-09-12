<?php

use App\Models\Customers\CustomerPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('gallery controller passes settings, photos, products, and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.gallery', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('photos')
        ->assertViewHas('products')
        ->assertViewHas('content')
        ->assertViewHas('storefrontTheme');
});

test('biscotto theme renders its bakery gallery presentation', function () {
    settings(['storefront_theme' => 'biscotto']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.gallery', [], false));

    $response->assertOk()
        ->assertSee('biscotto-gallery-hero', false)
        ->assertSee('biscotto-gallery-stage', false)
        ->assertSee('Fresh from the Oven');
});

test('can submit a gallery photo', function () {
    Storage::fake('public');

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('gallery.submit', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'caption' => 'My birthday cake!',
            'photo' => UploadedFile::fake()->image('cake.jpg'),
        ]);

    $response->assertRedirect();

    expect(CustomerPhoto::query()->count())->toBe(1)
        ->and(CustomerPhoto::query()->first()->customer_name)->toBe('Jane Doe');
});
