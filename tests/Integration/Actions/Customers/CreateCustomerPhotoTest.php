<?php

use App\Actions\Customers\CreateCustomerPhoto;
use App\Models\Customers\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it stores the photo and creates a customer photo record', function () {
    Storage::fake('public');

    $photo = UploadedFile::fake()->image('cake.jpg');

    $action = resolve(CreateCustomerPhoto::class);
    $customerPhoto = $action(
        photo: $photo,
        customerName: 'Jane Baker',
        customerEmail: 'jane@example.com',
        caption: 'My birthday cake',
        productId: null,
    );

    expect($customerPhoto)->toBeInstanceOf(CustomerPhoto::class)
        ->and($customerPhoto->customer_name)->toBe('Jane Baker')
        ->and($customerPhoto->customer_email)->toBe('jane@example.com')
        ->and($customerPhoto->caption)->toBe('My birthday cake');

    Storage::disk('public')->assertExists($customerPhoto->photo_path);
});

test('it stores the photo without its GPS metadata', function () {
    Storage::fake('public');

    $customerPhoto = resolve(CreateCustomerPhoto::class)(
        photo: UploadedFile::fake()->createWithContent('cake.jpg', jpegWithGpsExif()),
        customerName: 'Jane Baker',
        customerEmail: 'jane@example.com',
    );

    $stored = Storage::disk('public')->path($customerPhoto->photo_path);
    expect(exif_read_data($stored) ?: [])->not->toHaveKey('GPSLatitudeRef');
});
