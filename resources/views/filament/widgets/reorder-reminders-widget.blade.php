@php
    $count = $this->getLapsedCount();
    $customers = $this->getLapsedCustomers();
@endphp

<x-admin.dashboard.preview-card heading="Reorder Reminders" icon="🔄">
    <x-admin.dashboard.stat-row label="Customers needing a nudge" :value="$count" class="mb-1" />
    <div style="font-size: 0.6rem; color: var(--pw-card-text-muted); margin-bottom: 8px;">Ordered 2+ times, inactive 30+ days</div>

    @if (count($customers) > 0)
        @foreach ($customers as $customer)
            <x-admin.dashboard.list-row :value="$customer['last_order']">
                <span style="color: var(--pw-card-text); font-weight: 600;">{{ $customer['name'] }}</span>
                <span style="color: var(--pw-card-text-muted); margin-left: 6px; font-size: 0.65rem;">{{ $customer['email'] }}</span>
            </x-admin.dashboard.list-row>
        @endforeach
    @else
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">
            All regular customers are active — great job!
        </div>
    @endif
</x-admin.dashboard.preview-card>
