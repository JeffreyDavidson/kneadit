@php
    $rows = $this->getRows();
    $isMedium = $this->isSize('md');
@endphp

<x-admin.dashboard.preview-card heading="Low Stock Ingredients" icon="heroicon-o-cube">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
        <span class="pw-stat-label">{{ count($rows) }} item{{ count($rows) === 1 ? '' : 's' }} at risk</span>
        <a href="{{ $this->getViewAllUrl() }}" style="font-size: 0.65rem; color: var(--pw-card-accent); text-decoration: none;">View all →</a>
    </div>

    @forelse ($rows as $row)
        <x-admin.dashboard.list-row
            :dot-color="$row['status_color']"
            :value="'Reorder '.$row['reorder_qty'].' '.$row['unit']"
        >
            <span style="color: var(--pw-card-text); font-weight: 600;">{{ $row['name'] }}</span>
            <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $row['current_stock'] }} {{ $row['unit'] }}</span>
            @if ($isMedium && $row['supplier'])
                <span style="color: var(--pw-card-text-muted); margin-left: 6px;">· {{ $row['supplier'] }}</span>
            @endif
        </x-admin.dashboard.list-row>
    @empty
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">
            All stocked up!
        </div>
    @endforelse
</x-admin.dashboard.preview-card>
