<?php

use App\Filament\Pages\Tools\ProductImportExport;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ProductImportExport;
});

test('data defaults to empty array', function () {
    expect(test()->page->data)->toBeEmpty();
});

test('import results defaults to null', function () {
    expect(test()->page->importResults)->toBeNull();
});

test('preview data defaults to null', function () {
    expect(test()->page->previewData)->toBeNull();
});

test('preview errors defaults to null', function () {
    expect(test()->page->previewErrors)->toBeNull();
});

test('get view data includes import results', function () {
    $method = new ReflectionMethod(ProductImportExport::class, 'getViewData');
    $viewData = $method->invoke(test()->page);

    expect($viewData)->toHaveKeys(['importResults', 'previewData', 'previewErrors']);
});

test('get view data reflects current state', function () {
    test()->page->importResults = ['created' => 5, 'updated' => 2, 'errors' => []];
    test()->page->previewData = [['name' => 'Cookie', 'price' => 5.00]];
    test()->page->previewErrors = ['Row 2: missing name'];

    $method = new ReflectionMethod(ProductImportExport::class, 'getViewData');
    $viewData = $method->invoke(test()->page);

    expect($viewData['importResults']['created'])->toBe(5)
        ->and($viewData['previewData'])->toHaveCount(1)
        ->and($viewData['previewErrors'])->toHaveCount(1);
});
