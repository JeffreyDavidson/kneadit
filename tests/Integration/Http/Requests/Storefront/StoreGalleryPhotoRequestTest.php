<?php

use App\Http\Requests\Storefront\StoreGalleryPhotoRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $data = validGalleryPhotoData();
    unset($data[$field]);

    $validator = validator($data, (new StoreGalleryPhotoRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['customer_name', 'customer_email', 'photo']);

test('photo must be an image', function () {
    $validator = validator(
        array_merge(validGalleryPhotoData(), [
            'photo' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ]),
        (new StoreGalleryPhotoRequest)->rules(),
    );

    expect($validator->errors()->has('photo'))->toBeTrue();
});

test('photo over 5MB fails', function () {
    $validator = validator(
        array_merge(validGalleryPhotoData(), [
            'photo' => UploadedFile::fake()->image('huge.jpg')->size(5121),
        ]),
        (new StoreGalleryPhotoRequest)->rules(),
    );

    expect($validator->errors()->has('photo'))->toBeTrue();
});

test('valid submission passes', function () {
    $validator = validator(validGalleryPhotoData(), (new StoreGalleryPhotoRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

/** @return array<string, mixed> */
function validGalleryPhotoData(): array
{
    return [
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'photo' => UploadedFile::fake()->image('cake.jpg'),
    ];
}
