<?php

use App\Services\Inventory\ProductCsvUploadResolver;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('imports');
    Storage::fake('local');
});

test('resolves CSV files stored on the private import disk', function () {
    Storage::disk('imports')->put('products.csv', "name,price\nCookie,3.50\n");

    $file = resolve(ProductCsvUploadResolver::class)->resolve('products.csv');

    expect($file)
        ->not->toBeNull()
        ->and($file?->getClientOriginalName())->toBe('products.csv');
});

test('rejects paths outside the private import directory', function (string $path) {
    Storage::disk('imports')->put('not-a-csv.txt', 'not csv');

    expect(resolve(ProductCsvUploadResolver::class)->resolve($path))->toBeNull();
})->with([
    'traversal' => '../secret.csv',
    'absolute path' => '/tmp/secret.csv',
    'nested path' => 'nested/secret.csv',
    'wrong extension' => 'not-a-csv.txt',
    'Windows traversal' => '..\\secret.csv',
]);

test('rejects symlinks that escape the private import directory', function () {
    Storage::disk('local')->put('secret.csv', "secret\nvalue\n");

    $target = Storage::disk('local')->path('secret.csv');
    $link = Storage::disk('imports')->path('leak.csv');

    symlink($target, $link);

    expect(resolve(ProductCsvUploadResolver::class)->resolve('leak.csv'))->toBeNull();
});
