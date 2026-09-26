<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('RFM report exports segment summaries and sample customers to CSV', function () use ($storefrontUrl) {
    $page = authenticatedVisit("{$storefrontUrl}/admin/reports-center")
        ->waitForEvent('networkidle')
        ->click('RFM Segmentation')
        ->waitForEvent('networkidle');

    $page->script(<<<'JS'
        () => {
            window.__csvBlob = null;
            window.__csvFilename = null;
            window.__exportEvent = null;
            Livewire.on('export-csv', (payload) => {
                window.__exportEvent = payload;
            });
            window.URL.createObjectURL = (blob) => {
                window.__csvBlob = blob;

                return 'blob:reports-center-test';
            };
            window.URL.revokeObjectURL = () => {};
            HTMLAnchorElement.prototype.click = function () {
                window.__csvFilename = this.download;
            };
        }
        JS);

    $page->assertSee('Export CSV');
    $page->script(<<<'JS'
        Livewire.dispatch('export-csv', {
            type: 'rfm',
            data: {
                segments: {
                    champions: {
                        label: 'Champions',
                        count: 1,
                        description: 'Best customers',
                        sampleCustomers: [{
                            name: 'Sample Customer',
                            email: 'sample@example.test',
                            recency_days: 2,
                            frequency: 10,
                            monetary: 500,
                        }],
                    },
                },
            },
        });
        JS);
    $page->waitForEvent('networkidle');

    $csv = $page->script('async () => await window.__csvBlob?.text()');

    expect($page->script('window.__exportEvent'))->not->toBeNull()
        ->and($page->script('window.__csvFilename'))->toBe('rfm-report.csv')
        ->and($csv)->toContain('"Segment","Customers in Segment","Description","Sample Customer","Email","Recency (days)","Frequency","Monetary"')
        ->and($csv)->toContain('"Champions"');
})->group('launch-smoke');
