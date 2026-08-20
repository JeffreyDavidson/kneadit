<?php

use App\Filament\Pages\Tools\ProductImportExport;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ProductImportExport;
});

test('data defaults to empty array', function () {
    expect(testFixture('page', ProductImportExport::class)->data)->toBeEmpty();
});

test('import results defaults to null', function () {
    expect(testFixture('page', ProductImportExport::class)->importResults)->toBeNull();
});

test('preview data defaults to null', function () {
    expect(testFixture('page', ProductImportExport::class)->previewData)->toBeNull();
});

test('preview errors defaults to null', function () {
    expect(testFixture('page', ProductImportExport::class)->previewErrors)->toBeNull();
});

test('get view data includes import results', function () {
    $method = new ReflectionMethod(ProductImportExport::class, 'getViewData');
    $viewData = $method->invoke(testFixture('page', ProductImportExport::class));

    expect($viewData)->toHaveKeys(['importResults', 'previewData', 'previewErrors']);
});

test('get view data reflects current state', function () {
    testFixture('page', ProductImportExport::class)->importResults = ['created' => 5, 'updated' => 2, 'errors' => []];
    testFixture('page', ProductImportExport::class)->previewData = [['name' => 'Cookie', 'price' => 5.00]];
    testFixture('page', ProductImportExport::class)->previewErrors = ['Row 2: missing name'];

    $method = new ReflectionMethod(ProductImportExport::class, 'getViewData');
    $viewData = $method->invoke(testFixture('page', ProductImportExport::class));
    throw_unless(is_array($viewData), RuntimeException::class, 'Expected import view data.');
    $importResults = $viewData['importResults'] ?? null;
    throw_unless(is_array($importResults), RuntimeException::class, 'Expected import results.');

    expect($importResults['created'] ?? null)->toBe(5)
        ->and($viewData['previewData'] ?? null)->toHaveCount(1)
        ->and($viewData['previewErrors'] ?? null)->toHaveCount(1);
});
