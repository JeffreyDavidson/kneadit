@php
    $avg = $this->getAvgOrderValue();
    $newThisWeek = $this->getNewCustomersThisWeek();
    $repeatRate = $this->getRepeatCustomerRate();
@endphp

<x-admin.dashboard.preview-card heading="Customer Insights" icon="👥">
    <x-admin.dashboard.stat-row label="New This Week" :value="$newThisWeek" />
    <x-admin.dashboard.stat-row label="Repeat Rate" :value="$repeatRate.'%'" class="mt-2" />

    @unless ($this->isSize('sm'))
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--pw-card-border-subtle);">
            <span class="pw-stat-label">Avg Order Value</span>
            <span style="font-size: 1.4rem; font-weight: 700; color: var(--pw-card-text);">
                @money($avg['value'])
                <span style="font-size: 0.85rem; color: {{ $avg['trend'] === 'up' ? '#6b9e3a' : '#d4574a' }}; margin-left: 4px;">
                    {{ $avg['trend'] === 'up' ? '↑' : '↓' }}
                </span>
            </span>
        </div>
    @endunless
</x-admin.dashboard.preview-card>
