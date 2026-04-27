@php
    $rows = $this->getRows();
    $isLarge = $this->isSize('lg');
@endphp

<x-admin.dashboard.preview-card heading="At Risk Customers" icon="⚠️">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
        <span class="pw-stat-label">{{ count($rows) }} inactive</span>
        <a href="{{ $this->getViewAllUrl() }}" style="font-size: 0.65rem; color: var(--pw-card-accent); text-decoration: none;">View all →</a>
    </div>

    @forelse ($rows as $row)
        <x-admin.dashboard.list-row :value="$row['days_inactive'].'d inactive'">
            <a href="{{ $this->getCustomerEditUrl($row['id']) }}" style="color: var(--pw-card-accent); text-decoration: none;">{{ $row['name'] }}</a>
            <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $row['last_order'] }}</span>
            @if ($isLarge)
                <span style="color: var(--pw-card-text-muted); margin-left: 6px;">· LTV {{ $row['lifetime_value'] }}</span>
            @endif
        </x-admin.dashboard.list-row>
    @empty
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">
            No at-risk customers — everyone's recently active
        </div>
    @endforelse
</x-admin.dashboard.preview-card>
