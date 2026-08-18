<?php

use App\Services\Inventory\ProductCsvUploadResolver;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

test('resolves CSV files stored in the private import directory', function () {
    Storage::disk('local')->put('csv-imports/products.csv', "name,price\nCookie,3.50\n");

    $file = resolve(ProductCsvUploadResolver::class)->resolve('csv-imports/products.csv');

    expect($file)
        ->not->toBeNull()
        ->and($file?->getClientOriginalName())->toBe('products.csv');
});

test('rejects paths outside the private import directory', function (string $path) {
    Storage::disk('local')->put('secret.csv', "secret\nvalue\n");
    Storage::disk('local')->put('csv-imports/not-a-csv.txt', 'not csv');

    expect(resolve(ProductCsvUploadResolver::class)->resolve($path))->toBeNull();
})->with([
    'traversal' => 'csv-imports/../secret.csv',
    'absolute path' => '/tmp/secret.csv',
    'sibling directory' => 'secret.csv',
    'wrong extension' => 'csv-imports/not-a-csv.txt',
    'Windows traversal' => 'csv-imports\\..\\secret.csv',
]);

test('rejects symlinks that escape the private import directory', function () {
    Storage::disk('local')->put('secret.csv', "secret\nvalue\n");
    Storage::disk('local')->makeDirectory('csv-imports');

    $target = Storage::disk('local')->path('secret.csv');
    $link = Storage::disk('local')->path('csv-imports/leak.csv');

    symlink($target, $link);

    expect(resolve(ProductCsvUploadResolver::class)->resolve('csv-imports/leak.csv'))->toBeNull();
});
