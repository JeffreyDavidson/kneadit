@php
    $rows = $this->getRows();
    $isLarge = $this->isSize('lg');
@endphp

<x-admin.dashboard.preview-card heading="At Risk Customers" icon="heroicon-o-exclamation-triangle">
    <x-slot:actions>
        <a href="{{ $this->getViewAllUrl() }}" class="pw-card-action">View all</a>
    </x-slot:actions>

    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px">
        <span class="pw-stat-label">{{ count($rows) }} inactive</span>
    </div>

    @forelse ($rows as $row)
        <x-admin.dashboard.list-row :value="$row['days_inactive'] . 'd inactive'">
            <a
                href="{{ $this->getCustomerViewUrl($row['id']) }}"
                style="color: var(--pw-card-accent); text-decoration: none"
            >{{ $row['name'] }}</a>
            <span style="color: var(--pw-card-text-muted); margin-left: 6px">{{ $row['last_order'] }}</span>
            @if ($isLarge)
                <span style="color: var(--pw-card-text-muted); margin-left: 6px">· LTV {{ $row['lifetime_value'] }}</span>
            @endif
        </x-admin.dashboard.list-row>
    @empty
        <x-admin.dashboard.empty-state
            icon="heroicon-o-heart"
            title="No one needs a nudge"
            copy="Customers who go quiet after repeat orders will appear here for follow-up."
        />
    @endforelse
</x-admin.dashboard.preview-card>
