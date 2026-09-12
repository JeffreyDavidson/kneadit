<?php

use App\DataTransferObjects\Tenants\LegacyBakeryImportData;
use InvalidArgumentException;

it('stores dataset records and reports their counts', function () {
    $data = LegacyBakeryImportData::from([
        'categories' => [
            ['id' => 1, 'name' => 'Bread'],
            ['id' => 2, 'name' => 'Pastry'],
        ],
        'products' => [['id' => 3, 'name' => 'Sourdough']],
    ]);

    expect($data->toArray())->toBe([
        'categories' => [
            ['id' => 1, 'name' => 'Bread'],
            ['id' => 2, 'name' => 'Pastry'],
        ],
        'products' => [['id' => 3, 'name' => 'Sourdough']],
    ])
        ->and($data->counts())->toBe(['categories' => 2, 'products' => 1]);
});

it('rejects data that is not a list of record datasets', function (mixed $invalid) {
    expect(fn () => LegacyBakeryImportData::from($invalid))
        ->toThrow(InvalidArgumentException::class, 'The import file must contain an object of dataset arrays.');
})->with([
    'scalar' => 'invalid',
    'dataset scalar' => [['products' => 'invalid']],
    'associative records' => [['products' => ['first' => ['id' => 1]]]],
    'scalar record' => [['products' => [['id' => 1], 'invalid']]],
]);
