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
                total: 1,
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
        ->and($csv)->toContain('"Record Type","Segment","Customers in Segment","Description","Sample Customer","Email","Recency (days)","Frequency","Monetary","Total Customers"')
        ->and($csv)->toContain('"Champions"', '"1"');
})->group('launch-smoke');

test('other report CSV exports include summary and detail data', function () use ($storefrontUrl) {
    $page = authenticatedVisit("{$storefrontUrl}/admin/reports-center")
        ->waitForEvent('networkidle');

    $page->script(<<<'JS'
        () => {
            window.__csvDownloads = [];
            window.__csvBlobs = new Map();
            let nextBlobId = 0;
            window.URL.createObjectURL = (blob) => {
                const url = `blob:reports-center-${nextBlobId++}`;
                window.__csvBlobs.set(url, blob);

                return url;
            };
            window.URL.revokeObjectURL = () => {};
            HTMLAnchorElement.prototype.click = function () {
                window.__csvDownloads.push({ filename: this.download, blob: window.__csvBlobs.get(this.href) });
            };
        }
        JS);

    $page->script(<<<'JS'
        () => {
            const reports = [
                {
                    type: 'sales',
                    data: {
                        totalOrders: 12,
                        totalRevenue: 345.67,
                        avgOrderValue: 28.81,
                        ordersByStatus: { delivered: 10, pending: 2 },
                        topProducts: [{ name: 'Sourdough', units_sold: 8, revenue: 120.5 }],
                        revenueByDay: [{ date: '2026-09-25', revenue: 45.25 }],
                    },
                },
                {
                    type: 'customers',
                    data: {
                        newCustomers: 9,
                        repeatRate: 40,
                        repeatCustomers: 4,
                        totalCustomersWithOrders: 10,
                        topCustomers: [{ name: 'Avery Baker', email: 'avery@example.test', order_count: 3, total_spend: 90.25 }],
                        acquisitionByMonth: { '2026-09': 9 },
                    },
                },
                {
                    type: 'products',
                    data: {
                        products: [{
                            name: 'Croissant',
                            price: 4.5,
                            cost: 1.25,
                            margin: 72.2,
                            units_sold: 14,
                            revenue: 63,
                        }],
                    },
                },
                {
                    type: 'financial',
                    data: {
                        totalRevenue: 3000,
                        totalExpenses: 1250,
                        profit: 1750,
                        deductible: 800,
                        monthly: [{ month: 'Sep', revenue: 3000, expenses: 1250, profit: 1750 }],
                        expensesByCategory: [{ category: 'Supplies', amount: 250 }],
                    },
                },
                {
                    type: 'inventory',
                    data: {
                        usageWindowDays: 30,
                        totalItems: 7,
                        lowStockItems: 2,
                        outOfStockItems: 1,
                        ingredients: [{
                            name: 'Flour',
                            unit: 'lb',
                            current_stock: 4,
                            low_stock_threshold: 5,
                            is_low: true,
                            is_out: false,
                            daily_usage: 2,
                            daily_depletion: 2.5,
                            days_until_stockout: 2,
                            cost_per_unit: 0.75,
                        }],
                    },
                },
            ];

            reports.forEach(({ type, data }) => Livewire.dispatch('export-csv', { type, data }));
        }
        JS);

    $downloads = $page->script('async () => await Promise.all(window.__csvDownloads.map(async ({ filename, blob }) => ({ filename, csv: await blob.text() })))');
    $csvByFilename = collect($downloads)->keyBy('filename');

    expect($csvByFilename)->toHaveKeys([
        'sales-report.csv',
        'customers-report.csv',
        'products-report.csv',
        'financial-report.csv',
        'inventory-report.csv',
    ])
        ->and($csvByFilename['sales-report.csv']['csv'])->toContain('Total Orders', 'Average Order Value', 'Orders by Status', 'Revenue by Day', '345.67', 'delivered', '2026-09-25')
        ->and($csvByFilename['customers-report.csv']['csv'])->toContain('Repeat Customers', 'Total Customers with Orders', 'Acquisition by Month', 'Avery Baker', 'avery@example.test', '2026-09')
        ->and($csvByFilename['products-report.csv']['csv'])->toContain('Product', 'Price', 'Cost', 'Margin %', 'Units Sold', 'Revenue', 'Croissant', '72.2')
        ->and($csvByFilename['financial-report.csv']['csv'])->toContain('Total Revenue', 'Tax Deductible', 'Expenses by Category', 'Supplies', '1750')
        ->and($csvByFilename['inventory-report.csv']['csv'])->toContain('Total Items', 'Low Stock Items', 'Out of Stock Items', 'Usage Window Days', 'Flour', 'Low Stock', 'Cost per Unit');
})->group('launch-smoke');
