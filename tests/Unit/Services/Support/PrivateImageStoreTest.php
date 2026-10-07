<?php

use App\Services\Support\PrivateImageStore;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(fn () => Storage::fake('public'));

test('it stores a JPEG without its GPS metadata', function () {
    $upload = UploadedFile::fake()->createWithContent('cake.jpg', jpegWithGpsExif());

    $path = resolve(PrivateImageStore::class)->store($upload, 'customer-photos');

    $stored = Storage::disk('public')->path($path);
    expect(exif_read_data($upload->getPathname()))->toHaveKey('GPSLatitudeRef')
        ->and(exif_read_data($stored) ?: [])->not->toHaveKey('GPSLatitudeRef')
        ->and(Storage::disk('public')->get($path))->not->toContain('Exif')
        ->and(getimagesize($stored)[2])->toBe(IMAGETYPE_JPEG);
});

test('it applies the EXIF orientation before dropping it so photos are not stored sideways', function () {
    $upload = UploadedFile::fake()->createWithContent('cake.jpg', jpegWithGpsExif(orientation: 6));

    $path = resolve(PrivateImageStore::class)->store($upload, 'customer-photos');

    [$width, $height] = getimagesize(Storage::disk('public')->path($path));
    expect($width)->toBe(24)
        ->and($height)->toBe(32);
});

test('it keeps the file extension and puts the file in the given directory', function (string $name, string $extension) {
    $path = resolve(PrivateImageStore::class)->store(UploadedFile::fake()->image($name), 'review-photos');

    expect($path)->toStartWith('review-photos/')
        ->and($path)->toEndWith(".{$extension}");
    Storage::disk('public')->assertExists($path);
})->with([
    'jpeg' => ['a.jpg', 'jpg'],
    'png' => ['a.png', 'png'],
    'webp' => ['a.webp', 'webp'],
]);

test('it rejects a file that is not a readable image', function () {
    $upload = UploadedFile::fake()->createWithContent('cake.jpg', 'not an image');

    expect(fn () => resolve(PrivateImageStore::class)->store($upload, 'customer-photos'))
        ->toThrow(ValidationException::class)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});
