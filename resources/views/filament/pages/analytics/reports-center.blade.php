<x-filament-panels::page>
    <style @cspnonce>
        @media print {
            .no-print,
            .fi-header,
            .fi-sidebar,
            .fi-topbar,
            nav {
                display: none !important;
            }
            body {
                background: white;
            }
        }
        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 200px;
        }
        .bar-chart .bar {
            background: rgb(var(--primary-500));
            border-radius: 4px 4px 0 0;
            min-width: 20px;
            flex: 1;
            position: relative;
        }
        .bar-chart .bar-label {
            font-size: 10px;
            text-align: center;
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            white-space: nowrap;
            overflow: hidden;
        }
    </style>

    {{-- Report Selector --}}
    <div class="no-print mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ([
            'sales' => ['Sales Report', 'heroicon-o-currency-dollar'],
            'customers' => ['Customer Report', 'heroicon-o-users'],
            'products' => ['Product Performance', 'heroicon-o-cube'],
            'financial' => ['Financial Summary', 'heroicon-o-banknotes'],
            'inventory' => ['Inventory Report', 'heroicon-o-archive-box'],
            'rfm' => ['RFM Segmentation', 'heroicon-o-chart-bar-square'],
        ] as $key => [$label, $icon])
            <button
                wire:click="generateReport('{{ $key }}')"
                class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all
                    {{ $activeReport === $key ? 'border-primary-500 bg-primary-50 dark:bg-primary-950' : 'border-gray-200 dark:border-gray-700 hover:border-primary-300' }}"
            >
                <x-filament::icon :icon="$icon" class="text-primary-500 h-8 w-8" />
                <span class="text-center text-sm font-medium">{{ $label }}</span>
            </button>
        @endforeach
    </div>

    {{-- Date Controls --}}
    @if (in_array($activeReport, ['sales', 'customers', 'products']))
        <div class="no-print mb-6 flex flex-wrap items-end gap-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div>
                <label class="mb-1 block text-sm font-medium">Start Date</label>
                <input
                    type="date"
                    wire:model="startDate"
                    class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">End Date</label>
                <input
                    type="date"
                    wire:model="endDate"
                    class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                />
            </div>
            <button
                wire:click="generateReport('{{ $activeReport }}')"
                class="bg-primary-500 hover:bg-primary-600 rounded-lg px-4 py-2 text-white transition"
            >
                Generate
            </button>
        </div>
    @endif

    @if ($activeReport === 'inventory')
        <div class="no-print mb-6 flex flex-wrap items-end gap-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div>
                <label for="inventory-usage-window" class="mb-1 block text-sm font-medium">Lookback Period</label>
                <select
                    id="inventory-usage-window"
                    wire:model="inventoryUsageWindowDays"
                    class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                >
                    @foreach (collect([7, 30, 90, $inventoryUsageWindowDays])->unique()->sort() as $days)
                        <option value="{{ $days }}">{{ $days }} days</option>
                    @endforeach
                </select>
            </div>
            <button
                wire:click="generateReport('inventory')"
                class="bg-primary-500 hover:bg-primary-600 rounded-lg px-4 py-2 text-white transition"
            >
                Generate
            </button>
        </div>
    @endif

    @if ($activeReport === 'financial')
        <div class="no-print mb-6 flex items-end gap-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div>
                <label class="mb-1 block text-sm font-medium">Year</label>
                <select
                    wire:model="selectedYear"
                    class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                >
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button
                wire:click="generateReport('financial')"
                class="bg-primary-500 hover:bg-primary-600 rounded-lg px-4 py-2 text-white transition"
            >
                Generate
            </button>
        </div>
    @endif

    {{-- Action Buttons --}}
    @if ($activeReport && ! empty($reportData))
        <div class="no-print mb-6 flex gap-3">
            <button
                onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium transition hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
            >
                <x-filament::icon icon="heroicon-o-printer" class="h-4 w-4" /> Print
            </button>
            <button
                wire:click="exportCsv"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium transition hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
            >
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" /> Export CSV
            </button>
        </div>
    @endif

    {{-- Report Content --}}
    @if ($activeReport === 'sales' && ! empty($reportData))
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach ([
                    ['Total Orders', $reportData['totalOrders'], false],
                    ['Total Revenue', $reportData['totalRevenue'], true],
                    ['Avg Order Value', $reportData['avgOrderValue'], true],
                ] as [$label, $value, $isMoney])
                    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-sm text-gray-500">{{ $label }}</div>
                        <div class="text-2xl font-bold">
                            @if ($isMoney)
                                @money($value)
                            @else
                                {{ number_format($value) }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if (! empty($reportData['ordersByStatus']))
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold">Orders by Status</h3>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($reportData['ordersByStatus'] as $status => $count)
                            <div class="rounded-lg bg-gray-50 p-3 text-center dark:bg-gray-800">
                                <div class="text-lg font-bold">{{ $count }}</div>
                                <div class="text-sm text-gray-500 capitalize">{{ str_replace('_', ' ', $status) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (! empty($reportData['topProducts']))
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold">Top Products</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="py-2 text-left">Product</th>
                                <th class="py-2 text-right">Units Sold</th>
                                <th class="py-2 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['topProducts'] as $p)
                                <tr class="border-b dark:border-gray-800">
                                    <td class="py-2">{{ $p['name'] }}</td>
                                    <td class="py-2 text-right">{{ $p['units_sold'] }}</td>
                                    <td class="py-2 text-right">@money($p['revenue'])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (! empty($reportData['revenueByDay']))
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold">Revenue by Day</h3>
                    @php $maxRev = max(array_column($reportData['revenueByDay'], 'revenue')) ?: 1; @endphp
                    <div class="bar-chart pb-6">
                        @foreach ($reportData['revenueByDay'] as $day)
                            <div
                                class="bar"
                                style="height: {{ ($day['revenue'] / $maxRev) * 100 }}%"
                                title="{{ $day['date'] }}: @money($day['revenue'])"
                            >
                                <span class="bar-label">{{ \Carbon\Carbon::parse($day['date'])->format('m/d') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($activeReport === 'customers' && ! empty($reportData))
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach ([
                    ['New Customers', number_format($reportData['newCustomers'])],
                    ['Repeat Rate', $reportData['repeatRate'].'%'],
                    ['Active Customers', number_format($reportData['totalCustomersWithOrders'])],
                ] as [$label, $value])
                    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-sm text-gray-500">{{ $label }}</div>
                        <div class="text-2xl font-bold">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            @if (! empty($reportData['topCustomers']))
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold">Top Customers by Spend</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="py-2 text-left">Customer</th>
                                <th class="py-2 text-left">Email</th>
                                <th class="py-2 text-right">Orders</th>
                                <th class="py-2 text-right">Total Spend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['topCustomers'] as $c)
                                <tr class="border-b dark:border-gray-800">
                                    <td class="py-2">{{ $c['name'] }}</td>
                                    <td class="py-2 text-gray-500">{{ $c['email'] }}</td>
                                    <td class="py-2 text-right">{{ $c['order_count'] }}</td>
                                    <td class="py-2 text-right">@money($c['total_spend'])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (! empty($reportData['acquisitionByMonth']))
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold">Customer Acquisition by Month</h3>
                    @php $maxAcq = max($reportData['acquisitionByMonth']) ?: 1; @endphp
                    <div class="bar-chart pb-6">
                        @foreach ($reportData['acquisitionByMonth'] as $month => $count)
                            <div
                                class="bar"
                                style="height: {{ ($count / $maxAcq) * 100 }}%"
                                title="{{ $month }}: {{ $count }}"
                            >
                                <span class="bar-label">{{ $month }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($activeReport === 'products' && ! empty($reportData))
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <h3 class="mb-3 font-semibold">Product Performance</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="py-2 text-left">Product</th>
                            <th class="py-2 text-right">Price</th>
                            <th class="py-2 text-right">Cost</th>
                            <th class="py-2 text-right">Margin</th>
                            <th class="py-2 text-right">Units Sold</th>
                            <th class="py-2 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportData['products'] as $p)
                            <tr class="border-b dark:border-gray-800">
                                <td class="py-2">{{ $p['name'] }}</td>
                                <td class="py-2 text-right">@money($p['price'])</td>
                                <td class="py-2 text-right">@money($p['cost'])</td>
                                <td class="py-2 text-right">
                                    @if ($p['margin'] !== null)
                                        <span class="{{ $p['margin'] >= 50 ? 'text-success-600' : ($p['margin'] >= 30 ? 'text-warning-600' : 'text-danger-600') }}">
                                            {{ $p['margin'] }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right">{{ $p['units_sold'] }}</td>
                                <td class="py-2 text-right">@money($p['revenue'])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($activeReport === 'financial' && ! empty($reportData))
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                @foreach ([
                    ['Revenue', $reportData['totalRevenue'], 'text-success-600'],
                    ['Expenses', $reportData['totalExpenses'], 'text-danger-600'],
                    ['Profit', $reportData['profit'], $reportData['profit'] >= 0 ? 'text-success-600' : 'text-danger-600'],
                    ['Tax Deductible', $reportData['deductible'], 'text-primary-600'],
                ] as [$label, $value, $color])
                    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-sm text-gray-500">{{ $label }}</div>
                        <div class="text-2xl font-bold {{ $color }}">@money($value)</div>
                    </div>
                @endforeach
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-3 font-semibold">Monthly Breakdown</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="py-2 text-left">Month</th>
                            <th class="py-2 text-right">Revenue</th>
                            <th class="py-2 text-right">Expenses</th>
                            <th class="py-2 text-right">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportData['monthly'] as $m)
                            <tr class="border-b dark:border-gray-800">
                                <td class="py-2">{{ $m['month'] }}</td>
                                <td class="py-2 text-right">@money($m['revenue'])</td>
                                <td class="py-2 text-right">@money($m['expenses'])</td>
                                <td class="text-right py-2 {{ $m['profit'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                    @money($m['profit'])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (! empty($reportData['expensesByCategory']))
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold">Expenses by Category</h3>
                    <div class="space-y-2">
                        @php $maxExp = max(array_column($reportData['expensesByCategory'], 'amount')) ?: 1; @endphp
                        @foreach ($reportData['expensesByCategory'] as $cat)
                            <div class="flex items-center gap-3">
                                <div class="w-32 truncate text-sm">{{ $cat['category'] }}</div>
                                <div class="h-6 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div
                                        class="bg-primary-500 h-full rounded-full"
                                        style="width: {{ ($cat['amount'] / $maxExp) * 100 }}%"
                                    ></div>
                                </div>
                                <div class="w-24 text-right text-sm font-medium">@money($cat['amount'])</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($activeReport === 'inventory' && ! empty($reportData))
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach ([
                    ['Total Items', $reportData['totalItems']],
                    ['Low Stock', $reportData['lowStockItems']],
                    ['Out of Stock', $reportData['outOfStockItems']],
                ] as [$label, $value])
                    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div class="text-sm text-gray-500">{{ $label }}</div>
                        <div class="text-2xl font-bold {{ $label === 'Out of Stock' && $value > 0 ? 'text-danger-600' : ($label === 'Low Stock' && $value > 0 ? 'text-warning-600' : '') }}">
                            {{ $value }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-3 font-semibold">Inventory Details</h3>
                <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                    Daily Usage, Daily Depletion, and Days Left are based on the last {{ $reportData['usageWindowDays'] }} days
                    of stock movements.
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="py-2 text-left">Ingredient</th>
                                <th class="py-2 text-right">Stock</th>
                                <th class="py-2 text-right">Threshold</th>
                                <th class="py-2 text-right">Daily Usage</th>
                                <th
                                    class="py-2 text-right"
                                    title="Net daily stock movement, including waste and restocks"
                                >
                                    Daily Depletion
                                </th>
                                <th class="py-2 text-right">Days Left</th>
                                <th class="py-2 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['ingredients'] as $i)
                                <tr class="border-b dark:border-gray-800 {{ $i['is_out'] ? 'bg-danger-50 dark:bg-danger-950' : ($i['is_low'] ? 'bg-warning-50 dark:bg-warning-950' : '') }}">
                                    <td class="py-2">{{ $i['name'] }}</td>
                                    <td class="py-2 text-right">{{ $i['current_stock'] }} {{ $i['unit'] }}</td>
                                    <td class="py-2 text-right">{{ $i['low_stock_threshold'] }} {{ $i['unit'] }}</td>
                                    <td class="py-2 text-right">{{ $i['daily_usage'] }} {{ $i['unit'] }}</td>
                                    <td class="py-2 text-right">{{ $i['daily_depletion'] }} {{ $i['unit'] }}</td>
                                    <td class="py-2 text-right">
                                        {{ $i['days_until_stockout'] !== null ? $i['days_until_stockout'] . ' days' : '—' }}
                                    </td>
                                    <td class="py-2 text-right">
                                        @if ($i['is_out'])
                                            <span class="bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300 inline-flex rounded-full px-2 py-0.5 text-xs font-medium">Out</span>
                                        @elseif ($i['is_low'])
                                            <span class="bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300 inline-flex rounded-full px-2 py-0.5 text-xs font-medium">Low</span>
                                        @else
                                            <span class="bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300 inline-flex rounded-full px-2 py-0.5 text-xs font-medium">OK</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($activeReport === 'rfm' && ! empty($reportData))
        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-1 text-lg font-semibold">RFM Segmentation</h3>
                <p class="text-sm text-gray-500">
                    Segmenting {{ $reportData['total'] ?? 0 }} customers with at least one paid order by Recency,
                    Frequency, and Monetary value.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($reportData['segments'] ?? [] as $row)
                    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                        <div class="mb-3 flex items-start justify-between">
                            <div>
                                <p class="text-sm tracking-wider text-gray-500 uppercase">{{ $row['label'] }}</p>
                                <p class="text-3xl font-bold">{{ $row['count'] }}</p>
                            </div>
                            <x-filament::badge :color="$row['color']">{{ $row['label'] }}</x-filament::badge>
                        </div>
                        <p class="mb-4 text-sm text-gray-500">{{ $row['description'] }}</p>
                        @if (! empty($row['sampleCustomers']))
                            <div class="border-t border-gray-100 pt-3 dark:border-gray-800">
                                <p class="mb-2 text-xs font-semibold tracking-wider text-gray-500 uppercase">
                                    Sample customers
                                </p>
                                <ul class="space-y-1 text-sm">
                                    @foreach ($row['sampleCustomers'] as $c)
                                        <li class="flex items-center justify-between gap-2">
                                            <span class="truncate">{{ $c['name'] }}</span>
                                            <span class="text-xs whitespace-nowrap text-gray-500">
                                                {{ $c['frequency'] }}× · ${{ number_format($c['monetary'], 0) }} · {{ $c['recency_days'] }}d
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (! $activeReport)
        <div class="py-12 text-center text-gray-500">
            <x-filament::icon icon="heroicon-o-chart-pie" class="mx-auto mb-4 h-16 w-16 opacity-30" />
            <p class="text-lg">Select a report above to get started</p>
        </div>
    @endif

    <script @cspnonce>
        const registerCsvExportListener = () => {
            Livewire.on('export-csv', ({ data, type }) => {
                let rows = [];

                if (type === 'sales' && data.topProducts) {
                    rows = [['Record Type', 'Label', 'Date', 'Count', 'Amount', 'Units Sold']];
                    rows.push(['Summary', 'Total Orders', '', data.totalOrders, '', '']);
                    rows.push(['Summary', 'Total Revenue', '', '', data.totalRevenue, '']);
                    rows.push(['Summary', 'Average Order Value', '', '', data.avgOrderValue, '']);
                    Object.entries(data.ordersByStatus ?? {}).forEach(([status, count]) =>
                        rows.push(['Orders by Status', status, '', count, '', '']),
                    );
                    data.topProducts.forEach((product) =>
                        rows.push(['Top Product', product.name, '', '', product.revenue, product.units_sold]),
                    );
                    (data.revenueByDay ?? []).forEach((day) =>
                        rows.push(['Revenue by Day', '', day.date, '', day.revenue, '']),
                    );
                } else if (type === 'customers' && data.topCustomers) {
                    rows = [['Record Type', 'Label', 'Email', 'Count', 'Amount', 'Month']];
                    rows.push(['Summary', 'New Customers', '', data.newCustomers, '', '']);
                    rows.push(['Summary', 'Repeat Rate (%)', '', '', data.repeatRate, '']);
                    rows.push(['Summary', 'Repeat Customers', '', data.repeatCustomers, '', '']);
                    rows.push(['Summary', 'Total Customers with Orders', '', data.totalCustomersWithOrders, '', '']);
                    data.topCustomers.forEach((customer) =>
                        rows.push([
                            'Top Customer',
                            customer.name,
                            customer.email,
                            customer.order_count,
                            customer.total_spend,
                            '',
                        ]),
                    );
                    Object.entries(data.acquisitionByMonth ?? {}).forEach(([month, count]) =>
                        rows.push(['Acquisition by Month', '', '', count, '', month]),
                    );
                } else if (type === 'products' && data.products) {
                    rows = [['Product', 'Price', 'Cost', 'Margin %', 'Units Sold', 'Revenue']];
                    data.products.forEach((product) =>
                        rows.push([
                            product.name,
                            product.price,
                            product.cost,
                            product.margin,
                            product.units_sold,
                            product.revenue,
                        ]),
                    );
                } else if (type === 'financial' && data.monthly) {
                    rows = [['Record Type', 'Label', 'Amount', 'Revenue', 'Expenses', 'Profit']];
                    rows.push(['Summary', 'Total Revenue', data.totalRevenue, '', '', '']);
                    rows.push(['Summary', 'Total Expenses', data.totalExpenses, '', '', '']);
                    rows.push(['Summary', 'Profit', data.profit, '', '', '']);
                    rows.push(['Summary', 'Tax Deductible', data.deductible, '', '', '']);
                    data.monthly.forEach((month) =>
                        rows.push(['Monthly Breakdown', month.month, '', month.revenue, month.expenses, month.profit]),
                    );
                    (data.expensesByCategory ?? []).forEach((expense) =>
                        rows.push(['Expenses by Category', expense.category, expense.amount, '', '', '']),
                    );
                } else if (type === 'inventory' && data.ingredients) {
                    rows = [
                        [
                            'Record Type',
                            'Name',
                            'Unit',
                            'Current Stock',
                            'Low Stock Threshold',
                            'Low Stock',
                            'Out of Stock',
                            'Daily Usage',
                            'Daily Depletion',
                            'Days Until Stockout',
                            'Cost per Unit',
                            'Total Items',
                            'Low Stock Items',
                            'Out of Stock Items',
                            'Usage Window Days',
                        ],
                    ];
                    rows.push([
                        'Summary',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        data.totalItems,
                        data.lowStockItems,
                        data.outOfStockItems,
                        data.usageWindowDays,
                    ]);
                    data.ingredients.forEach((ingredient) =>
                        rows.push([
                            'Ingredient',
                            ingredient.name,
                            ingredient.unit,
                            ingredient.current_stock,
                            ingredient.low_stock_threshold,
                            ingredient.is_low,
                            ingredient.is_out,
                            ingredient.daily_usage,
                            ingredient.daily_depletion,
                            ingredient.days_until_stockout,
                            ingredient.cost_per_unit,
                            '',
                            '',
                            '',
                            data.usageWindowDays,
                        ]),
                    );
                } else if (type === 'rfm' && data.segments) {
                    rows = [
                        [
                            'Record Type',
                            'Segment',
                            'Customers in Segment',
                            'Description',
                            'Sample Customer',
                            'Email',
                            'Recency (days)',
                            'Frequency',
                            'Monetary',
                            'Total Customers',
                        ],
                    ];
                    rows.push(['Summary', '', '', '', '', '', '', '', '', data.total]);

                    Object.values(data.segments).forEach((segment) => {
                        const sampleCustomers = segment.sampleCustomers ?? [];

                        if (sampleCustomers.length === 0) {
                            rows.push([
                                'Segment',
                                segment.label,
                                segment.count,
                                segment.description,
                                '',
                                '',
                                '',
                                '',
                                '',
                                '',
                            ]);

                            return;
                        }

                        sampleCustomers.forEach((customer) =>
                            rows.push([
                                'Sample Customer',
                                segment.label,
                                segment.count,
                                segment.description,
                                customer.name,
                                customer.email,
                                customer.recency_days,
                                customer.frequency,
                                customer.monetary,
                                '',
                            ]),
                        );
                    });
                }

                if (rows.length === 0) return;

                const csv = rows
                    .map((r) => r.map((v) => '"' + String(v ?? '').replace(/"/g, '""') + '"').join(','))
                    .join('\n');
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = type + '-report.csv';
                a.click();
                URL.revokeObjectURL(url);
            });
        };

        if (window.Livewire) {
            registerCsvExportListener();
        } else {
            document.addEventListener('livewire:init', registerCsvExportListener, { once: true });
        }
    </script>
</x-filament-panels::page>
