<?php

use App\Enums\Customers\RfmSegment;
use App\Reports\Customers\RfmReport;
use Database\Seeders\BrowserTestFixtureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('browser fixtures seed a champion customer for the RFM report', function () {
    resolve(BrowserTestFixtureSeeder::class)->run();
    resolve(BrowserTestFixtureSeeder::class)->run();

    $report = resolve(RfmReport::class)->generate();

    expect($report->total)->toBe(1)
        ->and($report->segments[RfmSegment::Champions->value]->count)->toBe(1)
        ->and($report->segments[RfmSegment::Champions->value]->sampleCustomers[0]->email)
        ->toBe('browser-test-rfm@kneadit.test');
});
