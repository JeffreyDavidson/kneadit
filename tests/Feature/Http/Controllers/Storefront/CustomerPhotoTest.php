<?php

use App\Models\Customers\CustomerPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('gallery page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.gallery', [], false));

    $response->assertOk();
});

test('gallery shows only approved photos', function () {
    CustomerPhoto::factory()->approved()->create([
        'caption' => 'My beautiful cake',
    ]);

    CustomerPhoto::factory()->create([
        'customer_name' => 'Bob',
        'customer_email' => 'bob@example.com',
        'caption' => 'Pending photo',
        'photo_path' => 'photos/pending.jpg',
        'is_approved' => false,
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.gallery', [], false));

    $response->assertOk();
    $response->assertSee('My beautiful cake');
    $response->assertDontSee('Pending photo');
});

test('gallery hides unapproved photos', function () {
    CustomerPhoto::factory()->create([
        'caption' => 'Unapproved shot',
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get(route('storefront.gallery', [], false));

    $response->assertOk();
    $response->assertDontSee('Unapproved shot');
});

test('photo submission requires name and email', function () {
    Storage::fake('public');

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('gallery.submit', [], false), [
            'photo' => UploadedFile::fake()->image('cake.jpg'),
        ]);

    $response->assertSessionHasErrors(['customer_name', 'customer_email']);
});

test('photo submission saves to database as unapproved', function () {
    Storage::fake('public');

    withoutMiddleware(tenantMiddleware())
        ->post(route('gallery.submit', [], false), [
            'customer_name' => 'Carol',
            'customer_email' => 'carol@example.com',
            'caption' => 'My order arrived!',
            'photo' => UploadedFile::fake()->image('order.jpg'),
        ]);

    $photo = CustomerPhoto::query()->firstOrFail();

    expect($photo)->not->toBeNull()->and($photo->is_approved)->toBeFalse()->and($photo->customer_name)->toBe('Carol');
});
