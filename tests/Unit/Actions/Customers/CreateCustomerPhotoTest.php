<?php

use App\Actions\Customers\CreateCustomerPhoto;
use App\Models\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it stores the photo and creates a customer photo record', function () {
    Storage::fake('public');

    $photo = UploadedFile::fake()->image('cake.jpg');

    $action = new CreateCustomerPhoto;
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
