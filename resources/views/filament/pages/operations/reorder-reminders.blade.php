@use(App\Services\Settings\TenantSettings)
<x-filament-panels::page>
    @php
        $customers = $this->getCustomers();
        $criticalCount = $customers->where('days_since', '>=', 120)->count();
        $warningCount = $customers->where('days_since', '>=', 90)->where('days_since', '<', 120)->count();
        $mildCount = $customers->where('days_since', '<', 90)->count();
        $totalRevAtRisk = $customers->sum('total_spent');
    @endphp

    {{-- Page banner --}}
    <x-admin.page-banner title="Customer Reorder Reminders">
        <div class="flex items-center gap-2.5">
            <span class="text-[0.8rem] text-white/60">Inactive for</span>
            <select wire:model.live="threshold"
                    class="appearance-none rounded-full border border-white/25 bg-white/15 pl-3.5 pr-8 py-1.5 text-[0.8rem] font-semibold text-white cursor-pointer min-w-[7rem]"
                    style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22white%22 stroke-width=%222.5%22><polyline points=%226 9 12 15 18 9%22/></svg>'); background-repeat: no-repeat; background-position: right 0.625rem center; background-size: 0.75rem;">
                @foreach ([30, 60, 90, 120] as $days)
                    <option value="{{ $days }}" class="text-brand-900 bg-white">{{ $days }}+ days</option>
                @endforeach
            </select>
        </div>
    </x-admin.page-banner>

    @if ($customers->isEmpty())
        <x-admin.empty-state
            icon="heroicon-o-check-circle"
            title="All customers are active!"
            subtitle="No one has been inactive for more than {{ $threshold }} days. Great retention!"
        />
    @else
        {{-- Stats --}}
        <x-admin.stat-grid :cols="4" data-stat-grid>
            <x-admin.stat-card label="Need Outreach" :value="$customers->count()" />
            <x-admin.stat-card label="Critical (120+ days)" :value="$criticalCount" tone="danger" />
            <x-admin.stat-card label="Warning (90+ days)" :value="$warningCount" tone="warning" />
            <x-admin.stat-card label="Revenue at Risk" :value="'$' . number_format($totalRevAtRisk, 0)" tone="brand-600" />
        </x-admin.stat-grid>

        {{-- Customer table --}}
        <x-admin.card title="Inactive Customers" :subtitle="$customers->count() . ' ' . Str::plural('customer', $customers->count())">
            <x-admin.data-table data-admin-table>
                <x-slot:head>
                    <th>Customer</th>
                    <th>Last Order</th>
                    <th>Days Inactive</th>
                    <th class="text-center">Orders</th>
                    <th class="text-right">Total Spent</th>
                    <th class="text-right">Action</th>
                </x-slot:head>
                @foreach ($customers as $customer)
                    @php
                        $urgency = $customer->days_since >= 120 ? 'cancelled' : ($customer->days_since >= 90 ? 'pending' : 'confirmed');
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <x-admin.avatar :name="$customer->customer_name" size="sm" />
                                <div>
                                    <div class="font-semibold text-brand-900 text-sm">{{ $customer->customer_name }}</div>
                                    <div class="text-xs text-brand-500">{{ $customer->customer_email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-brand-900 text-[0.85rem]">{{ \Carbon\Carbon::parse($customer->last_order_date)->format('M j, Y') }}</td>
                        <td>
                            <x-admin.badge :type="$urgency" :label="$customer->days_since . ' days'" />
                        </td>
                        <td class="text-center font-semibold text-brand-900">{{ $customer->total_orders }}</td>
                        <td class="text-right font-bold text-brand-900">@money($customer->total_spent)</td>
                        <td class="text-right">
                            @php
                                $subject = rawurlencode('We miss you at ' . app(TenantSettings::class)->storeName . '!');
                                $body = rawurlencode("Hi {$customer->customer_name},\n\nIt's been a while since your last visit and we miss you! We've been baking up some amazing new treats and would love to see you again.\n\nVisit us to place your next order.\n\nWarmly,\n" . app(TenantSettings::class)->storeName);
                            @endphp
                            <x-admin.btn variant="primary" :href="'mailto:' . $customer->customer_email . '?subject=' . $subject . '&body=' . $body" icon="heroicon-o-envelope" size="sm">
                                Send Reminder
                            </x-admin.btn>
                        </td>
                    </tr>
                @endforeach
            </x-admin.data-table>
        </x-admin.card>
    @endif
</x-filament-panels::page>
