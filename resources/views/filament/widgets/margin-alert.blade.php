@php
    $rows = $this->getRows();
    $isMedium = $this->isSize('md');
@endphp

<x-admin.dashboard.preview-card heading="Margin Alerts (below 30%)" icon="heroicon-o-banknotes">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
        <span class="pw-stat-label">{{ count($rows) }} product{{ count($rows) === 1 ? '' : 's' }} at risk</span>
    </div>

    @forelse ($rows as $row)
        @php $marginColor = $row['margin'] < 15 ? '#dc2626' : '#e8b04a'; @endphp
        <x-admin.dashboard.list-row :value="$row['margin_formatted']" :dot-color="$marginColor">
            <span style="color: var(--pw-card-text); font-weight: 600;">{{ $row['name'] }}</span>
            @if ($isMedium)
                <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $row['price'] }} sell · {{ $row['cost'] }} cost</span>
            @endif
        </x-admin.dashboard.list-row>
    @empty
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem; display: flex; justify-content: center; align-items: center; gap: 4px;">
            <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
            No low-margin products
        </div>
    @endforelse
</x-admin.dashboard.preview-card>
