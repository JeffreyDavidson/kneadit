<?php

use App\Models\CustomerPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('gallery page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())->get('/gallery');

    $response->assertOk();
});

test('gallery shows only approved photos', function () {
    CustomerPhoto::query()->create([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'caption' => 'My beautiful cake',
        'photo_path' => 'photos/approved.jpg',
        'is_approved' => true,
    ]);

    CustomerPhoto::query()->create([
        'customer_name' => 'Bob',
        'customer_email' => 'bob@example.com',
        'caption' => 'Pending photo',
        'photo_path' => 'photos/pending.jpg',
        'is_approved' => false,
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get('/gallery');

    $response->assertOk();
    $response->assertSee('My beautiful cake');
    $response->assertDontSee('Pending photo');
});

test('gallery hides unapproved photos', function () {
    CustomerPhoto::query()->create([
        'customer_name' => 'Eve',
        'customer_email' => 'eve@example.com',
        'caption' => 'Unapproved shot',
        'photo_path' => 'photos/unapproved.jpg',
        'is_approved' => false,
    ]);

    $response = withoutMiddleware(tenantMiddleware())->get('/gallery');

    $response->assertOk();
    $response->assertDontSee('Unapproved shot');
});

test('photo submission requires name and email', function () {
    Storage::fake('public');

    $response = withoutMiddleware(tenantMiddleware())
        ->post('/gallery', [
            'photo' => UploadedFile::fake()->image('cake.jpg'),
        ]);

    $response->assertSessionHasErrors(['customer_name', 'customer_email']);
});

test('photo submission saves to database as unapproved', function () {
    Storage::fake('public');

    withoutMiddleware(tenantMiddleware())
        ->post('/gallery', [
            'customer_name' => 'Carol',
            'customer_email' => 'carol@example.com',
            'caption' => 'My order arrived!',
            'photo' => UploadedFile::fake()->image('order.jpg'),
        ]);

    $photo = CustomerPhoto::query()->first();

    expect($photo)->not->toBeNull();
    expect($photo->is_approved)->toBeFalse();
    expect($photo->customer_name)->toBe('Carol');
});
