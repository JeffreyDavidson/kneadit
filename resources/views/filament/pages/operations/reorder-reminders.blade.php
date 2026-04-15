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
        <div style="display: flex; align-items: center; gap: 0.625rem;">
            <span style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">Inactive for</span>
            <select wire:model.live="threshold" style="appearance: none; -webkit-appearance: none; padding: 0.4rem 2rem 0.4rem 0.875rem; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.15) url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22white%22 stroke-width=%222.5%22><polyline points=%226 9 12 15 18 9%22/></svg>') no-repeat right 0.625rem center; background-size: 0.75rem; color: white; font-size: 0.8rem; font-weight: 600; cursor: pointer; min-width: 7rem;">
                <option value="30" style="color: var(--brand-900); background: white;">30+ days</option>
                <option value="60" style="color: var(--brand-900); background: white;">60+ days</option>
                <option value="90" style="color: var(--brand-900); background: white;">90+ days</option>
                <option value="120" style="color: var(--brand-900); background: white;">120+ days</option>
            </select>
        </div>
    </x-admin.page-banner>

    @if ($customers->isEmpty())
        <x-admin.empty-state
            icon="🎉"
            title="All customers are active!"
            subtitle="No one has been inactive for more than {{ $threshold }} days. Great retention!"
        />
    @else
        {{-- Stats --}}
        <x-admin.stat-grid :cols="4" data-stat-grid>
            <x-admin.stat-card label="Need Outreach" :value="$customers->count()" />
            <x-admin.stat-card label="Critical (120+ days)" :value="$criticalCount" color="var(--status-danger)" />
            <x-admin.stat-card label="Warning (90+ days)" :value="$warningCount" color="var(--status-warning)" />
            <x-admin.stat-card label="Revenue at Risk" :value="'$' . number_format($totalRevAtRisk, 0)" color="var(--brand-600)" />
        </x-admin.stat-grid>

        {{-- Customer table --}}
        <x-admin.card title="Inactive Customers" :subtitle="$customers->count() . ' ' . Str::plural('customer', $customers->count())">
            <x-admin.data-table data-admin-table>
                <x-slot:head>
                    <th>Customer</th>
                    <th>Last Order</th>
                    <th>Days Inactive</th>
                    <th style="text-align: center;">Orders</th>
                    <th style="text-align: right;">Total Spent</th>
                    <th style="text-align: right;">Action</th>
                </x-slot:head>
                @foreach ($customers as $customer)
                    @php
                        $urgency = $customer->days_since >= 120 ? 'cancelled' : ($customer->days_since >= 90 ? 'pending' : 'confirmed');
                    @endphp
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.625rem;">
                                <x-admin.avatar :name="$customer->customer_name" size="sm" />
                                <div>
                                    <div style="font-weight: 600; color: var(--brand-900); font-size: 0.875rem;">{{ $customer->customer_name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--brand-500);">{{ $customer->customer_email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color: var(--brand-900); font-size: 0.85rem;">{{ \Carbon\Carbon::parse($customer->last_order_date)->format('M j, Y') }}</td>
                        <td>
                            <x-admin.badge :type="$urgency" :label="$customer->days_since . ' days'" />
                        </td>
                        <td style="text-align: center; font-weight: 600; color: var(--brand-900);">{{ $customer->total_orders }}</td>
                        <td style="text-align: right; font-weight: 700; color: var(--brand-900);">@money($customer->total_spent)</td>
                        <td style="text-align: right;">
                            @php
                                $subject = rawurlencode('We miss you at ' . app(\App\Services\Settings\TenantSettings::class)->storeName . '!');
                                $body = rawurlencode("Hi {$customer->customer_name},\n\nIt's been a while since your last visit and we miss you! We've been baking up some amazing new treats and would love to see you again.\n\nVisit us to place your next order.\n\nWarmly,\n" . app(\App\Services\Settings\TenantSettings::class)->storeName . " 🍪");
                            @endphp
                            <x-admin.btn variant="primary" :href="'mailto:' . $customer->customer_email . '?subject=' . $subject . '&body=' . $body" icon="✉️" size="sm">
                                Send Reminder
                            </x-admin.btn>
                        </td>
                    </tr>
                @endforeach
            </x-admin.data-table>
        </x-admin.card>
    @endif
</x-filament-panels::page>
