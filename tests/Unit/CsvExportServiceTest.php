<?php

use App\Services\Export\CsvExportService;

it('returns valid export types', function () {
    $service = new CsvExportService;

    expect($service->validTypes())->toBe(['products', 'categories', 'orders', 'customers', 'reviews']);
});
