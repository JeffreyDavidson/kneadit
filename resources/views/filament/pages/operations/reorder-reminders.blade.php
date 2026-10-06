<x-filament-panels::page>
    @php
        $customers = $this->getCustomers();
        $criticalCount = $customers->where('days_since', '>=', 120)->count();
        $warningCount = $customers->where('days_since', '>=', 90)->where('days_since', '<', 120)->count();
        $mildCount = $customers->where('days_since', '<', 90)->count();
        $totalRevAtRisk = $customers->sum('total_spent');
    @endphp

    {{-- Page banner --}}
    <x-tenant-admin.page-banner title="Customer Reorder Reminders">
        <div class="flex items-center gap-2.5">
            <span class="text-brand-400 text-[0.8rem]">Inactive for</span>
            <select
                wire:model.live="threshold"
                class="text-brand-50 min-w-[7rem] cursor-pointer appearance-none rounded-full border border-(--kn-border-control) bg-(--kn-surface) py-1.5 pr-8 pl-3.5 text-[0.8rem] font-semibold"
                style="
                    background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23a07a55%22 stroke-width=%222.5%22><polyline points=%226 9 12 15 18 9%22/></svg>');
                    background-repeat: no-repeat;
                    background-position: right 0.625rem center;
                    background-size: 0.75rem;
                "
            >
                @foreach ([30, 60, 90, 120] as $days)
                <option value="{{ $days }}" class="text-brand-50 bg-(--kn-surface)">{{ $days }}+ days</option>
                @endforeach
            </select>
        </div>
    </x-tenant-admin.page-banner>

    @if ($customers->isEmpty())
        <x-tenant-admin.empty-state
            icon="heroicon-o-check-circle"
            title="All customers are active!"
            subtitle="No one has been inactive for more than {{ $threshold }} days. Great retention!"
        />
    @else
        {{-- Stats --}}
        <x-tenant-admin.stat-grid :cols="4" data-stat-grid>
            <x-tenant-admin.stat-card label="Need Outreach" :value="$customers->count()" />
            <x-tenant-admin.stat-card label="Critical (120+ days)" :value="$criticalCount" tone="danger" />
            <x-tenant-admin.stat-card label="Warning (90+ days)" :value="$warningCount" tone="warning" />
            <x-tenant-admin.stat-card
                label="Revenue at Risk"
                :value="'$'.number_format($totalRevAtRisk, 0)"
                tone="brand-600"
            />
        </x-tenant-admin.stat-grid>

        {{-- Customer table --}}
        <x-tenant-admin.card
            title="Inactive Customers"
            :subtitle="$customers->count().' '.Str::plural('customer', $customers->count())"
        >
            <x-tenant-admin.data-table data-admin-table>
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
                                <x-tenant-admin.avatar :name="$customer->customer_name" size="sm" />
                                <div>
                                    <div class="text-sm font-semibold text-(--kn-ink)">
                                        {{ $customer->customer_name }}
                                    </div>
                                    <div class="text-brand-500 text-xs">{{ $customer->customer_email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-[0.85rem] text-(--kn-ink)">
                            {{ \Carbon\Carbon::parse($customer->last_order_date)->format('M j, Y') }}
                        </td>
                        <td>
                            <x-tenant-admin.badge :type="$urgency" :label="$customer->days_since.' days'" />
                        </td>
                        <td class="text-center font-semibold text-(--kn-ink)">{{ $customer->total_orders }}</td>
                        <td class="text-right font-bold text-(--kn-ink)">@money($customer->total_spent)</td>
                        <td class="text-right">
                            <x-tenant-admin.btn
                                variant="primary"
                                :href="$this->reminderMailto($customer)"
                                icon="heroicon-o-envelope"
                                size="sm"
                            >
                                Send Reminder
                            </x-tenant-admin.btn>
                        </td>
                    </tr>
                @endforeach
            </x-tenant-admin.data-table>
        </x-tenant-admin.card>
    @endif
</x-filament-panels::page>
